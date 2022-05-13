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
    ORDER_DIRECTION_BUY, ORDERS_REFRESH_INTERVAL,
} from '../constants';
import IntervalSelector from "./IntervalSelector/IntervalSelector";
import FormatHelper from "../Helpers/FormatHelper";
import UserSettingsModal from "./UserSettings/UserSettingsModal";
import RequestHelper from "../Helpers/RequestHelper";
import Loading from "./Loading/Loading";
import StateProvider, {stateContext} from "./StateProvider";
import TimeIntervals from "../TimeIntervals";

const App = () => {
    const [state, actions] = useContext(stateContext)
    const isLoggedIn = state.user !== null;

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
            actions.setUser(null);
        })
        RequestHelper.fetch('/api/user', {}, response => {
            if (response.data !== undefined) {
                actions.setUser(response.data);
            }
            actions.setInitialized(true);
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
        if (isLoggedIn) {
            actions.setWSClient(new BinanceWebsocketClient(function(price) {
                actions.setCurrentPrice(1.0*price);
            }, 'BTCBUSD'));
        } else if (state.wsClient !== null) {
            state.wsClient.close()
            actions.setWSClient(null)
        }
        return null;
    }, [isLoggedIn]);

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
                actions.setUser(null);
            }
        });
    }

    useEffect(() => {
        if (isLoggedIn) {
            RequestHelper.fetch('/api/mdclusters/BTCUSD', {}, response => {
                actions.setMdClusters(response.data);
            });
        }
    }, [state.interval, isLoggedIn]);

    const mdClustersAnnotations = useMemo(() => {
        const annotations = [];
        if (state.mdClusters === undefined || state.mdClusters.length === 0) {
            return [];
        }
        state.mdClusters.forEach((mdCluster, i) => {
            const borderColor = i !== 0 ? chartsLinesColor : '#00ff00';
            const relativePriceDiffPercent = 100*(mdCluster.toPrice-mdCluster.fromPrice)/mdCluster.fromPrice;
            const opacity = i === 0 ? 0.7 : 0.3;
            let annotation = {
                x: Math.round(mdCluster.fromTime - TimeIntervals.FIVE_MINUTES / 2),
                x2: Math.round(mdCluster.toTime - TimeIntervals.FIVE_MINUTES / 2),
                strokeDashArray: 0,
                borderColor: borderColor,
                fillColor: '#244B4B',
                opacity: opacity,
            };
            if (i === 0) {
                annotation.label = {
                    text: FormatHelper.formatAmount(mdCluster.marketDelta) + ', ' + (Math.round(relativePriceDiffPercent*100)/100) + '%',
                    borderColor: chartsLinesColor,
                    style: {
                        color: chartsTextColor,
                        background: 'transparent',
                        opacity: opacity,
                    },
                };
            }
            annotations.push(annotation);
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

    const orderAnnotations = useMemo(() => {
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
                    text: 'Order ' + order.id + ': ' + FormatHelper.formatPrice(order.price),
                    textAnchor: 'start',
                    position: 'left',
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
                        text: 'Order ' + order.id + ' SL: ' + FormatHelper.formatPrice(order.sl),
                        textAnchor: 'start',
                        position: 'left',
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
                        text: 'Order ' + order.id + ' TP: ' + FormatHelper.formatPrice(order.tp),
                        textAnchor: 'start',
                        position: 'left',
                    }
                });
            }
        }
        return result;
    }, [state.orders]);

    useEffect(() => {
        if (isLoggedIn) {
            RequestHelper.fetch('/api/orders?page=' + state.ordersPage, {}, response => {
                actions.setOrders(response.data, response.meta.current_page, response.meta.last_page);
            });
        }
    }, [state.ordersPage, state.ordersPagesTotal, state.ordersReRender, isLoggedIn]);

    useEffect(() => {
        if (isLoggedIn) {
            RequestHelper.fetch('/api/orders?history=1&page=' + state.ordersHistoryPage, {}, response => {
                actions.setOrdersHistory(response.data, response.meta.current_page, response.meta.last_page);
            });
        }
    }, [state.ordersHistoryPage, state.ordersHistoryPagesTotal, state.ordersReRender, isLoggedIn]);

    useEffect(() => {
        setInterval(() => {
            actions.ordersReRender();
        }, ORDERS_REFRESH_INTERVAL);
    }, []);

    if (state.initialized === false) {
        return <Loading />
    }

    const annotations = [...mdClustersAnnotations, getToTimeAnnotation()];
    const popupDom = <Alert variant={state.popup.type} onClose={() => hidePopup()} dismissible>
                         <Alert.Heading>{state.popup.title}</Alert.Heading>
                         <p>{state.popup.message}</p>
                     </Alert>;
    let content = '';
    if (isLoggedIn) {
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
                                        yAnnotations={orderAnnotations}
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
        content = <LoginForm onSuccess={onLoginSuccess} onFail={onLoginFail} />
    }
    return ( <div id="page">
                {isLoggedIn ? (
                    <div id="top">
                        <div className="top-left">
                            <a href="/" className="logo-link">
                                <img src="images/logo.png" />
                            </a>
                        </div>
                        <div className="top-right">
                            <button type="button" className="btn btn-primary" onClick={() => onLogoutClick()}>{state.user.name}&nbsp;<i className="fa fa-arrow-right"></i></button>&nbsp;&nbsp;&nbsp;
                            <button type="button" className="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#userSettingsModal"><i className="fa fa-gear"></i></button>
                        </div>
                    </div>
                ) : ''}
                <div id="middle">
                    {content}
                </div>
            </div> );
}

if (document.getElementById('app')) {
    ReactDOM.render(
        <StateProvider>
            <App/>
        </StateProvider>,
        document.getElementById('app')
    );
}
