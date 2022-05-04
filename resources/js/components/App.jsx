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

const App = () => {
    const updateInterval = 15000;
    const priceHeight = 400;
    const mdHeight = 250;
    const popupDefault = {
        show: false,
        type: 'success',
        message: 'TEST',
        title: '',
    };
    const chartsTextColor = '#A39ED8';
    const chartsLinesColor = '#635E98';
    let popupTimeout = null;

    FormatHelper.setFromSign('₿');
    FormatHelper.setToSign('$');

    const [chartsInterval, setChartsInterval] = useState(5*60000);
    const [currentPrice, setCurrentPrice] = useState(0.0);
    const [popup, setPopup] = useState(popupDefault);
    const [isLoggedIn, setIsLoggedIn] = useState(LoginHelper.isLoggedIn());
    const [orders, setOrders] = useState([]);
    const [signals, setSignals] = useState([]);

    const priceChartRef = useRef();
    const mdChartRef = useRef();
    const ordersListRef = useRef();

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
        refreshOrders();
        setInterval(() => {
            refreshOrders();
        }, 3000);
    }, []);

    const xAnnotations = useMemo(() => {
        var annotations = [];
        const buyColor = '#00E396';
        const sellColor = '#E30096';
        if (signals.length === 0) {
            return [];
        }
        signals.forEach((signal) => {
            if (signal.signal === StrategySignals.BUY) {
                const x = Math.round(signal.time - chartsInterval/2);
                const x2 = x + chartsInterval;
                annotations.push({
                    x: x,
                    x2: x2,
                    strokeDashArray: 0,
                    borderColor: chartsLinesColor,
                    fillColor: '#244B4B',
                    opacity: 0.7,
                });
            }
        });
        return annotations;
    }, [signals]);

    let daysForInterval = TimeHelper.daysForInterval(chartsInterval)
    if (daysForInterval > 3) {
        daysForInterval = 3;
    }
    let fromDate = TimeHelper.subDaysFromDate(new Date(), daysForInterval);
    let toDate = new Date();
    let fromTime = chartsInterval*parseInt(fromDate.getTime()/chartsInterval);
    let toTime = chartsInterval*(parseInt(toDate.getTime()/chartsInterval)+1);

    useEffect(() => {
        RequestHelper.fetch('/api/signals/BTCUSDT/' + fromTime + '/' + toTime + '/' + chartsInterval, {}, response => {
            setSignals(response.data);
        });
    }, [fromTime, toTime, chartsInterval]);

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
                        <div className="card-header">Price<CurrentPrice symbol={"BTCBUSD"} /></div>
                        <div className="card-body pt-0">
                            <div className="chart">
                                <ordersContext.Provider value={orders}>
                                    <PriceChart fromTime={fromTime} toTime={toTime} interval={chartsInterval} height={priceHeight} currentPrice={currentPrice} textColor={chartsTextColor} linesColor={chartsLinesColor} innerRef={priceChartRef} xAnnotations={xAnnotations} />
                                </ordersContext.Provider>
                                <MarketDeltaChart fromTime={fromTime} toTime={toTime} interval={chartsInterval} height={mdHeight} updateInterval={updateInterval} textColor={chartsTextColor} linesColor={chartsLinesColor} innerRef={mdChartRef} xAnnotations={xAnnotations} />
                            </div>
                        </div>
                    </div>
                    <br/>
                    <IntervalSelector setChartsInterval={setChartsInterval} />
                    <br/>
                    <ordersContext.Provider value={orders}>
                        <OrdersList innerRef={ordersListRef} />
                    </ordersContext.Provider>
                </div>
                <div className="col-md-2 ps-3">
                    <OrderForm currentPrice={currentPrice} showPopup={showPopup} ordersList={ordersListRef.current} />
                </div>
            </div>
            <UserSettingsModal showPopup={showPopup} />
        </div>
    } else {
        loginButton = <button type="button" className="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loginForm">Login</button>;
        content = <LoginForm onSuccess={onLoginSuccess} onFail={onLoginFail} />
    }

    return (
        <currentPriceContext.Provider value={currentPrice}>
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
        </currentPriceContext.Provider>
    );
}

if (document.getElementById('app')) {
    ReactDOM.render(<App/>, document.getElementById('app'));
}
