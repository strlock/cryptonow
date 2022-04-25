import React, {useState} from "react";
import ReactDOM from 'react-dom';
import MarketDeltaChart from './MarketDeltaChart';
import PriceChart from './PriceChart';
import TimeIntervals from '../TimeIntervals';
import TimeHelper from "../Helpers/TimeHelper";
import OrderForm from "./OrderForm";
import BinanceWebsocketClient from "./BinanceWebsocketClient";
import "regenerator-runtime/runtime";
import Alert from 'react-bootstrap/Alert';
import LoginForm from "./LoginForm";
import LoginHelper from "../Helpers/LoginHelper";
import OrdersList from "./OrdersList";

function App() {
    const [interval, setInterval] = useState(5*60000);
    const [updateInterval, setUpdateInterval] = useState(15000);
    const [priceHeight, setPriceHeight] = useState(400);
    const [mdHeight, setMdHeight] = useState(250);
    const [currentPrice, setCurrentPrice] = useState(0);
    const popupDefaultState = {
        show: false,
        type: 'success',
        message: 'TEST',
        title: '',
    };
    const [popup, setPopup] = useState(popupDefaultState);
    const [isLoggedIn, setIsLoggedIn] = useState(LoginHelper.isLoggedIn());

    //const fromCurrencySign = '₿';
    const toCurrencySign = '$';
    let popupTimeout = null;

    const showPopup = (message, type, title) => {
        setPopup({
            show: true,
            type: type,
            message: message,
            title: title,
        });
        clearTimeout(popupTimeout);
        popupTimeout = setTimeout(function() {
            setPopup(popupDefaultState);
        }, 3000);
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

    const wsClient = new BinanceWebsocketClient(function(price) {
        setCurrentPrice(1.0*price);
    });

    let daysForInterval = TimeHelper.daysForInterval(interval)
    if (daysForInterval > 3) {
        daysForInterval = 3;
    }
    let fromDate = TimeHelper.subDaysFromDate(new Date(), daysForInterval);
    let toDate = new Date();
    let fromTime = interval*parseInt(fromDate.getTime()/interval);
    let toTime = interval*(parseInt(toDate.getTime()/interval)+1);
    const popupDom = <Alert variant={popup.type} onClose={() => setPopup(popupDefaultState)} dismissible>
        <Alert.Heading>{popup.title}</Alert.Heading>
        <p>{popup.message}</p>
    </Alert>;
    let loginButton = '';
    let content = '';
    if (isLoggedIn) {
        loginButton = <button type="button" className="btn btn-primary" onClick={() => onLogoutClick()}>Logout ({LoginHelper.getLoggedInUserName()})</button>;
        content =                     <div className="container">
            {popup.show ? popupDom : ''}
            <div className="row justify-content-center">
                <div className="col-md-10">
                    <div className="card">
                        <div className="card-header">
                            <button onClick={() => setInterval(TimeIntervals.ONE_MINUTE)} className="btn btn-primary btn-sm">1m</button>&nbsp;
                            <button onClick={() => setInterval(TimeIntervals.FIVE_MINUTES)} className="btn btn-secondary btn-sm">5m</button>&nbsp;
                            <button onClick={() => setInterval(TimeIntervals.FIFTEEN_MINUTES)} className="btn btn-primary btn-sm">15m</button>&nbsp;
                            <button onClick={() => setInterval(TimeIntervals.THIRTEEN_MINUTES)} className="btn btn-secondary btn-sm">30m</button>&nbsp;
                            <button onClick={() => setInterval(TimeIntervals.ONE_HOUR)} className="btn btn-primary btn-sm">1h</button>&nbsp;
                            <button onClick={() => setInterval(TimeIntervals.FOUR_HOURS)} className="btn btn-secondary btn-sm">4h</button>&nbsp;
                            <button onClick={() => setInterval(TimeIntervals.ONE_DAY)} className="btn btn-primary btn-sm">1d</button>
                            <div>{fromDate.toLocaleString()} - {toDate.toLocaleString()} - {daysForInterval}d</div>
                        </div>
                    </div><br/>
                    <div className="card">
                        <div className="card-header">Price{currentPrice !== 0.0 ? ': ' + currentPrice.toFixed(2) + toCurrencySign : ''}</div>
                        <div className="card-body">
                            <PriceChart fromTime={fromTime} toTime={toTime} interval={interval} height={priceHeight} updateInterval={updateInterval} />
                        </div>
                    </div><br/>
                    <div className="card">
                        <div className="card-header">Market Statistics</div>
                        <div className="card-body">
                            <MarketDeltaChart fromTime={fromTime} toTime={toTime} interval={interval} height={mdHeight} updateInterval={updateInterval} />
                        </div>
                    </div><br/>
                    <div className="card">
                        <div className="card-header">Orders</div>
                        <div className="card-body">
                            <OrdersList />
                        </div>
                    </div>
                </div>
                <div className="col-md-2 ps-3">
                    <OrderForm currentPrice={currentPrice} showPopup={showPopup} />
                </div>
            </div>
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
                    {loginButton}
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
