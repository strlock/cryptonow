import React, {useState, useEffect, useRef} from "react";
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
    POPUP_TIMEOUT
} from '../constants';
import IntervalSelector from "./IntervalSelector";
import FormatHelper from "../Helpers/FormatHelper";
import UserSettingsModal from "./UserSettingsModal";

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
    let popupTimeout = null;

    FormatHelper.setFromSign('₿');
    FormatHelper.setToSign('$');

    const [chartsInterval, setChartsInterval] = useState(5*60000);
    const [currentPrice, setCurrentPrice] = useState(0);
    const [popup, setPopup] = useState(popupDefault);
    const [isLoggedIn, setIsLoggedIn] = useState(LoginHelper.isLoggedIn());

    const priceChartRef = useRef();
    const mdChartRef = useRef();
    const ordersListRef = useRef();

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
        showPopup('Login successfull');
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

    const refreshCharts = () => {
        const priceChart = priceChartRef.current;
        const mdChart = mdChartRef.current;
        priceChart.refresh();
        mdChart.refresh();
    }

    new BinanceWebsocketClient(function(price) {
        setCurrentPrice(1.0*price);
    });

    useEffect(() => {
        const interval = setInterval(() => {
            refreshCharts();
        }, REFRESH_INTERVAL);
        return () => clearInterval(interval);
    }, []);

    let daysForInterval = TimeHelper.daysForInterval(chartsInterval)
    if (daysForInterval > 3) {
        daysForInterval = 3;
    }
    let fromDate = TimeHelper.subDaysFromDate(new Date(), daysForInterval);
    let toDate = new Date();
    let fromTime = chartsInterval*parseInt(fromDate.getTime()/chartsInterval);
    let toTime = chartsInterval*(parseInt(toDate.getTime()/chartsInterval)+1);
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
                    <IntervalSelector setChartsInterval={setChartsInterval} refreshCharts={refreshCharts} />
                    <br/>
                    <PriceChart fromTime={fromTime} toTime={toTime} interval={chartsInterval} height={priceHeight} currentPrice={currentPrice} ref={priceChartRef} />
                    <br/>
                    <MarketDeltaChart fromTime={fromTime} toTime={toTime} interval={chartsInterval} height={mdHeight} updateInterval={updateInterval} ref={mdChartRef} />
                    <br/>
                    <OrdersList ref={ordersListRef} />
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
    );
}

if (document.getElementById('app')) {
    ReactDOM.render(<App/>, document.getElementById('app'));
}
