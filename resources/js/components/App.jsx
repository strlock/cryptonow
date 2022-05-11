import React, {useState, useEffect, useRef, useMemo, useReducer, useContext} from "react";
import ReactDOM from 'react-dom';
import MarketDeltaChart from './MarketDeltaChart/MarketDeltaChart';
import PriceChart from './PriceChart/PriceChart';
import TimeHelper from "../Helpers/TimeHelper";
import OrderForm from "./OrderForm/OrderForm";
import BinanceWebsocketClient from "./BinanceWebsocketClient";
import "regenerator-runtime/runtime";
import Alert from 'react-bootstrap/Alert';
import LoginForm from "./Login/LoginForm";
import LoginHelper from "../Helpers/LoginHelper";
import OrdersList from "./OrdersList/OrdersList";
import {
    POPUP_TIMEOUT,
    ORDER_DIRECTION_BUY,
} from '../constants';
import IntervalSelector from "./IntervalSelector/IntervalSelector";
import FormatHelper from "../Helpers/FormatHelper";
import UserSettingsModal from "./UserSettings/UserSettingsModal";
import RequestHelper from "../Helpers/RequestHelper";
import Loading from "./Loading/Loading";
import StateProvider, {stateContext} from "./StateProvider";

const App = () => {
    const [state, actions] = useContext(stateContext)

    const updateInterval = 15000;
    const priceHeight = 400;
    const mdHeight = 250;
    const chartsTextColor = '#A39ED8';
    const chartsLinesColor = '#635E98';
    let popupTimeout = null;

    const priceChartRef = useRef();
    const mdChartRef = useRef();
    const ordersListRef = useRef();

    useEffect(() => {
        RequestHelper.setExpiredTokenCallback(() => {
            LoginHelper.clearAccessToken();
            actions.setUser(false);
        })
        RequestHelper.fetch('/api/user', {}, response => {
            if (response.data !== undefined) {
                actions.setUser(response.data);
            } else {
                actions.setUser(false);
            }
        });
    }, []);

    FormatHelper.setFromSign('₿');
    FormatHelper.setToSign('$');

    let daysForInterval = TimeHelper.daysForInterval(state.interval)
    if (daysForInterval > 10) {
        daysForInterval = 10;
    }
    let fromTime = TimeHelper.round((TimeHelper.subDaysFromDate(new Date(), daysForInterval)).getTime(), state.interval);
    let toTime = TimeHelper.round((new Date()).getTime(), state.interval);

    useEffect(() => {
        new BinanceWebsocketClient(function(price) {
            actions.setCurrentPrice(1.0*price);
        }, 'BTCBUSD');
    }, []);

    const showPopup = (message, type, title) => {
        actions.setPopup({
            show: true,
            type: type,
            message: message,
            title: title,
        });
        clearTimeout(popupTimeout);
        popupTimeout = setTimeout(function() {
            actions.resetPopup();
        }, POPUP_TIMEOUT);
    }

    const hidePopup = () => {
        actions.resetPopup();
    }

    const onLoginSuccess = (user) => {
        actions.setUser(user);
    }

    const onLoginFail = (message) => {
        showPopup(message, 'danger');
    }

    const onLogoutClick = () => {
        RequestHelper.fetch('/api/logout', {method: 'POST'}, response => {
            if (response.success) {
                LoginHelper.clearAccessToken();
                actions.setUser(false);
            }
        });
    }

    useEffect(() => {
        RequestHelper.fetch('/api/mdclusters/BTCUSD/' + state.interval, {}, response => {
            actions.setMdClusters(response.data);
        });
    }, [state.interval, state.user]);

    const mdClustersAnnotations = useMemo(() => {
        const annotations = [];
        if (state.mdClusters === undefined || state.mdClusters.length === 0) {
            return [];
        }
        state.mdClusters.forEach((mdCluster, i) => {
            const borderColor = i !== 0 ? chartsLinesColor : '#00ff00';
            const relativePriceDiffPercent = 100*(mdCluster.toPrice-mdCluster.fromPrice)/mdCluster.fromPrice;
            const opacity = i === 0 ? 0.7 : 0.3;
            annotations.push({
                x: Math.round(mdCluster.fromTime - state.interval / 2),
                x2: Math.round(mdCluster.toTime - state.interval / 2),
                strokeDashArray: 0,
                borderColor: borderColor,
                fillColor: '#244B4B',
                opacity: opacity,
                label: {
                    text: FormatHelper.formatAmount(mdCluster.marketDelta) + ', ' + (Math.round(relativePriceDiffPercent*100)/100) + '%',
                    borderColor: chartsLinesColor,
                    style: {
                        color: chartsTextColor,
                        background: 'transparent',
                        opacity: opacity,
                    },
                }
            });
        });
        return annotations;
    }, [state.mdClusters]);

    const getToTimeAnnotation = () => {
        return {
            x: Math.round(toTime - state.interval / 2),
            x2: null,
            strokeDashArray: 0,
            borderColor: '#00ff00',
            label: {
                text: (new Date(toTime)).toLocaleTimeString(),
                borderColor: chartsLinesColor,
                style: {
                    color: chartsTextColor,
                    background: 'transparent'
                },
            }
        };
    }

    const yAnnotations = useMemo(() => {
        const result = [];
        const buyColor = '#00E396';
        const sellColor = '#E30096';
        for(let i in state.orders) {
            const order = state.orders[i];
            result.push({
                y: order.price,
                borderColor: order.direction === ORDER_DIRECTION_BUY ? buyColor : sellColor,
                strokeDashArray: 0,
                label: {
                    borderColor: chartsLinesColor,
                    style: {
                        color: chartsTextColor,
                        background: 'transparent'
                    },
                    text: 'Order ' + order.id + ': ' + FormatHelper.formatPrice(order.price)
                }
            });
            if (order.sl) {
                result.push({
                    y: order.sl,
                    borderColor: order.direction === ORDER_DIRECTION_BUY ? buyColor : sellColor,
                    strokeDashArray: 5,
                    label: {
                        borderColor: chartsLinesColor,
                        style: {
                            color: chartsTextColor,
                            background: 'transparent'
                        },
                        text: 'Order ' + order.id + ' SL: ' + FormatHelper.formatPrice(order.sl)
                    }
                });
            }
            if (order.tp) {
                result.push({
                    y: order.tp,
                    borderColor: order.direction === ORDER_DIRECTION_BUY ? buyColor : sellColor,
                    strokeDashArray: 5,
                    label: {
                        borderColor: chartsLinesColor,
                        style: {
                            color: chartsTextColor,
                            background: 'transparent'
                        },
                        text: 'Order ' + order.id + ' TP: ' + FormatHelper.formatPrice(order.tp)
                    }
                });
            }
        }
        return result;
    }, [state.orders]);

    useEffect(() => {
        RequestHelper.fetch('/api/orders?page=' + state.ordersPage, {}, response => {
            actions.setOrders(response.data, response.meta.current_page, response.meta.last_page);
        });
    }, [state.ordersPage, state.ordersPagesTotal, state.ordersReRender]);

    useEffect(() => {
        RequestHelper.fetch('/api/orders?history=1&page=' + state.ordersHistoryPage, {}, response => {
            actions.setOrdersHistory(response.data, response.meta.current_page, response.meta.last_page);
        });
    }, [state.ordersHistoryPage, state.ordersHistoryPagesTotal, state.ordersReRender]);

    const annotations = [...mdClustersAnnotations, getToTimeAnnotation()];
    const popupDom = <Alert variant={state.popup.type} onClose={() => hidePopup()} dismissible>
                         <Alert.Heading>{state.popup.title}</Alert.Heading>
                         <p>{state.popup.message}</p>
                     </Alert>;
    let loginButton = '';
    let content = '';
    let settingsButton = '';
    if (state.user !== null) {
        if (state.user !== false) {
            loginButton = <button type="button" className="btn btn-primary" onClick={() => onLogoutClick()}>{state.user.name}&nbsp;<i className="fa fa-arrow-right"></i></button>;
            settingsButton = <button type="button" className="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#userSettingsModal"><i className="fa fa-gear"></i></button>;
            content = <div className="container">
                    <div className="row justify-content-center">
                        <div className="col-xl-12">
                            {state.popup.show ? popupDom : ''}
                        </div>
                        <div className="col-xl-12">
                            <div className="card">
                                <div className={"card-header"}>
                                    <IntervalSelector />
                                </div>
                                <div className="card-body pt-0">
                                    <div className="chart">
                                        <PriceChart
                                            fromTime={fromTime}
                                            toTime={toTime}
                                            height={priceHeight}
                                            textColor={chartsTextColor}
                                            linesColor={chartsLinesColor}
                                            innerRef={priceChartRef}
                                            xAnnotations={annotations}
                                            yAnnotations={yAnnotations}
                                            orders={state.orders} />
                                        <MarketDeltaChart
                                            fromTime={fromTime}
                                            toTime={toTime}
                                            height={mdHeight}
                                            updateInterval={updateInterval}
                                            textColor={chartsTextColor}
                                            linesColor={chartsLinesColor}
                                            innerRef={mdChartRef}
                                            xAnnotations={annotations} />
                                    </div>
                                </div>
                            </div>
                            <br/>
                            <div className="card">
                                <div className="card-body">
                                    <OrdersList
                                        innerRef={ordersListRef}
                                        orders={state.orders}
                                        ordersHistory={state.ordersHistory}
                                        ordersPage={state.ordersPage}
                                        ordersHistoryPage={state.ordersHistoryPage}
                                        ordersPagesTotal={state.ordersPagesTotal}
                                        ordersHistoryPagesTotal={state.ordersHistoryPagesTotal}
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                    <UserSettingsModal showPopup={showPopup} />
                    <OrderForm showPopup={showPopup} />
                </div>
        } else {
            loginButton = <button type="button" className="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loginForm">Login</button>;
            content = <LoginForm onSuccess={onLoginSuccess} onFail={onLoginFail} />
        }
    }
    return (
        state.user !== null ?
            <div id="page">
                <div id="top">
                    <div className="top-left">
                        <a href="/" className="logo-link">
                            <img src="images/logo.png" />
                        </a>
                    </div>
                    <div className="top-right">
                        {loginButton}&nbsp;&nbsp;&nbsp;
                        {settingsButton}
                    </div>
                </div>
                <div id="middle">
                    {content}
                </div>
            </div>
            : <Loading />
    );
}

if (document.getElementById('app')) {
    ReactDOM.render(
        <StateProvider>
            <App/>
        </StateProvider>,
        document.getElementById('app')
    );
}
