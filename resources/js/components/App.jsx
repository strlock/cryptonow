import React, {useState, useEffect, useRef, useMemo} from "react";
import ReactDOM from 'react-dom';
import MarketDeltaChart from './MarketDeltaChart';
import PriceChart from './PriceChart';
import TimeHelper from "../Helpers/TimeHelper";
import OrderForm from "./OrderForm";
import BinanceWebsocketClient from "./BinanceWebsocketClient";
import "regenerator-runtime/runtime";
import Alert from 'react-bootstrap/Alert';
import LoginForm from "./LoginForm";
import LoginHelper from "../Helpers/LoginHelper";
import OrdersList from "./OrdersList";
import {
    REFRESH_INTERVAL,
    POPUP_TIMEOUT, StrategySignals
} from '../constants';
import IntervalSelector from "./IntervalSelector";
import FormatHelper from "../Helpers/FormatHelper";
import UserSettingsModal from "./UserSettingsModal";
import currentPriceContext from "../contexts/CurrentPriceContext";
import ordersContext from "../contexts/OrdersContext";
import RequestHelper from "../Helpers/RequestHelper";
import CurrentPrice from "./CurrentPrice";
import TimeIntervals from "../TimeIntervals";

const App = () => {
    const updateInterval = 15000;
    const priceHeight = 400;
    const mdHeight = 250;
    const popupDefault = {
        show: false,
        type: 'success',
        message: '',
        title: '',
    };
    const chartsTextColor = '#A39ED8';
    const chartsLinesColor = '#635E98';
    let popupTimeout = null;

    FormatHelper.setFromSign('₿');
    FormatHelper.setToSign('$');

    const [interval, setChartsInterval] = useState(TimeIntervals.FIVE_MINUTES);
    const [currentPrice, setCurrentPrice] = useState(0.0);
    const [popup, setPopup] = useState(popupDefault);
    const [isLoggedIn, setIsLoggedIn] = useState(LoginHelper.isLoggedIn());
    const [orders, setOrders] = useState([]);
    const [mdClusters, setMdClusters] = useState([]);

    const priceChartRef = useRef();
    const mdChartRef = useRef();
    const ordersListRef = useRef();

    let daysForInterval = TimeHelper.daysForInterval(interval)
    if (daysForInterval > 10) {
        daysForInterval = 10;
    }
    let fromTime = TimeHelper.round((TimeHelper.subDaysFromDate(new Date(), daysForInterval)).getTime(), interval);
    let toTime = TimeHelper.round((new Date()).getTime(), interval);

    useEffect(() => {
        new BinanceWebsocketClient(function(price) {
            setCurrentPrice(1.0*price);
        }, 'BTCBUSD');
    }, []);

    const refreshOrders = () => {
        RequestHelper.fetch('/api/orders', {}, response => {
            if (response.data !== undefined) {
                setOrders(response.data);
            }
        });
    }

    const showPopup = (message, type, title) => {
        setPopup({
            show: true,
            type: type,
            message: message,
            title: title,
        });
        clearTimeout(popupTimeout);
        popupTimeout = setTimeout(function() {
            setPopup(popupDefault);
        }, POPUP_TIMEOUT);
    }

    const hidePopup = () => {
        setPopup(popupDefault);
    }

    const onLoginSuccess = (accessToken, userName) => {
        LoginHelper.login(accessToken, userName);
        setIsLoggedIn(LoginHelper.isLoggedIn());
    }

    const onLoginFail = (message) => {
        showPopup(message, 'danger');
        setIsLoggedIn(LoginHelper.isLoggedIn());
    }

    const onLogoutClick = () => {
        LoginHelper.logout();
        setIsLoggedIn(LoginHelper.isLoggedIn());
    }

    useEffect(() => {
        RequestHelper.fetch('/api/mdclusters/BTCUSDT/' + interval, {}, response => {
            setMdClusters(response.data);
        });
    }, [interval]);

    const mdClustersAnnotations = useMemo(() => {
        const annotations = [];
        if (mdClusters === undefined || mdClusters.length === 0) {
            return [];
        }
        mdClusters.forEach((mdCluster, i) => {
            const borderColor = i !== 0 ? chartsLinesColor : '#00ff00';
            const relativePriceDiffPercent = 100*(mdCluster.toPrice-mdCluster.fromPrice)/mdCluster.fromPrice;
            annotations.push({
                x: Math.round(mdCluster.fromTime - interval / 2),
                x2: Math.round(mdCluster.toTime - interval / 2),
                strokeDashArray: 0,
                borderColor: borderColor,
                fillColor: '#244B4B',
                opacity: 0.7,
                label: {
                    text: FormatHelper.formatAmount(mdCluster.marketDelta) + ', ' + (Math.round(relativePriceDiffPercent*100)/100) + '%',
                    borderColor: chartsLinesColor,
                    style: {
                        color: chartsTextColor,
                        background: 'transparent'
                    },
                }
            });
        });
        return annotations;
    }, [mdClusters]);

    const getToTimeAnnotation = () => {
        return {
            x: Math.round(toTime - interval / 2),
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

    const annotations = [...mdClustersAnnotations, getToTimeAnnotation()];

    useEffect(() => {
        refreshOrders();
        setInterval(() => {
            refreshOrders();
        }, 3000);
    }, []);

    const popupDom = <Alert variant={popup.type} onClose={() => hidePopup()} dismissible>
                         <Alert.Heading>{popup.title}</Alert.Heading>
                         <p>{popup.message}</p>
                     </Alert>;
    let loginButton = '';
    let content = '';
    let settingsButton = '';
    if (isLoggedIn) {
        loginButton = <button type="button" className="btn btn-primary" onClick={() => onLogoutClick()}>Logout ({LoginHelper.getLoggedInUserName()})</button>;
        settingsButton = <button type="button" className="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#userSettingsModal">Settings</button>;
        content = <div className="container">
            {popup.show ? popupDom : ''}
            <div className="row justify-content-center">
                <div className="col-md-10">
                    <div className="card">
                        <div className={"card-header"}>
                            <IntervalSelector chartsInterval={interval} setChartsInterval={setChartsInterval} />
                        </div>
                        <div className="card-body pt-0">
                            <div className="chart">
                                <currentPriceContext.Provider value={currentPrice}>
                                <ordersContext.Provider value={orders}>
                                    <PriceChart fromTime={fromTime} toTime={toTime} interval={interval} height={priceHeight} currentPrice={currentPrice} textColor={chartsTextColor} linesColor={chartsLinesColor} innerRef={priceChartRef} xAnnotations={annotations} />
                                </ordersContext.Provider>
                                </currentPriceContext.Provider>
                                <MarketDeltaChart fromTime={fromTime} toTime={toTime} interval={interval} height={mdHeight} updateInterval={updateInterval} textColor={chartsTextColor} linesColor={chartsLinesColor} innerRef={mdChartRef} xAnnotations={annotations} />
                            </div>
                        </div>
                    </div>
                    <br/>
                    <ordersContext.Provider value={orders}>
                        <OrdersList innerRef={ordersListRef} />
                    </ordersContext.Provider>
                </div>
                <div className="col-md-2 ps-3">
                    <currentPriceContext.Provider value={currentPrice}>
                    <OrderForm currentPrice={currentPrice} showPopup={showPopup} ordersList={ordersListRef.current} />
                    </currentPriceContext.Provider>
                </div>
            </div>
            <UserSettingsModal showPopup={showPopup} />
        </div>
    } else {
        loginButton = <button type="button" className="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loginForm">Login</button>;
        content = <LoginForm onSuccess={onLoginSuccess} onFail={onLoginFail} />
    }

    return (
        <div id="page">
            {/*<span style={{color: '#fff'}}>{(new Date(fromTime)).toLocaleString()} - {(new Date(toTime)).toLocaleString()}</span>*/}
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
    );
}

if (document.getElementById('app')) {
    ReactDOM.render(<App/>, document.getElementById('app'));
}
