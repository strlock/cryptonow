import React, { Component } from "react";
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

class App extends React.Component {
    wsClient = null

    state = {
        interval: 5*60000,
        updateInterval: 15000,
        time: 0,
        priceHeight: 400,
        mdHeight: 250,
        fromTime: 0,
        toTime: 0,
        currentPrice: 0,
        alert: {
            show: false,
            type: 'success',
            message: 'TEST',
            title: '',
        },
        isLoggedIn: LoginHelper.isLoggedIn(),
    }

    fromCurrencySign = '₿'
    toCurrencySign = '$'
    alertTimeout = null

    constructor(props) {
        super(props);
        let self = this;
        this.wsClient = new BinanceWebsocketClient(function(price) {
            self.setState({currentPrice: 1.0*price});
        });
    }

    render() {
        let self = this;
        let daysForInterval = TimeHelper.daysForInterval(this.state.interval)
        if (daysForInterval > 3) {
            daysForInterval = 3;
        }
        let fromDate = TimeHelper.subDaysFromDate(new Date(), daysForInterval);
        let toDate = new Date();
        this.state.fromTime = this.state.interval*parseInt(fromDate.getTime()/this.state.interval);
        this.state.toTime = this.state.interval*(parseInt(toDate.getTime()/this.state.interval)+1);
        const alert = <Alert variant={this.state.alert.type} onClose={() => this.setState({alert:{show:false}})} dismissible>
            <Alert.Heading>{this.state.alert.title}</Alert.Heading>
            <p>{this.state.alert.message}</p>
        </Alert>;
        let loginButton = '';
        let content = '';
        if (this.state.isLoggedIn) {
            loginButton = <button type="button" className="btn btn-primary" onClick={() => this.onLogoutClick.call(this)}>Logout ({LoginHelper.getLoggedInUserName()})</button>;
            content =                     <div className="container">
                {this.state.alert.show ? alert : ''}
                <div className="row justify-content-center">
                    <div className="col-md-10">
                        <div className="card">
                            <div className="card-header">
                                <button onClick={() => this.setState({interval: TimeIntervals.ONE_MINUTE})} className="btn btn-primary btn-sm">1m</button>&nbsp;
                                <button onClick={() => this.setState({interval: TimeIntervals.FIVE_MINUTES})} className="btn btn-secondary btn-sm">5m</button>&nbsp;
                                <button onClick={() => this.setState({interval: TimeIntervals.FIFTEEN_MINUTES})} className="btn btn-primary btn-sm">15m</button>&nbsp;
                                <button onClick={() => this.setState({interval: TimeIntervals.THIRTEEN_MINUTES})} className="btn btn-secondary btn-sm">30m</button>&nbsp;
                                <button onClick={() => this.setState({interval: TimeIntervals.ONE_HOUR})} className="btn btn-primary btn-sm">1h</button>&nbsp;
                                <button onClick={() => this.setState({interval: TimeIntervals.FOUR_HOURS})} className="btn btn-secondary btn-sm">4h</button>&nbsp;
                                <button onClick={() => this.setState({interval: TimeIntervals.ONE_DAY})} className="btn btn-primary btn-sm">1d</button>
                                <div>{fromDate.toLocaleString()} - {toDate.toLocaleString()} - {daysForInterval}d</div>
                            </div>
                        </div><br/>
                        <div className="card">
                            <div className="card-header">Price{this.state.currentPrice !== 0.0 ? ': ' + this.state.currentPrice.toFixed(2) + this.toCurrencySign : ''}</div>
                            <div className="card-body">
                                <PriceChart fromTime={this.state.fromTime} toTime={this.state.toTime} interval={this.state.interval} height={this.state.priceHeight} updateInterval={this.state.updateInterval} />
                            </div>
                        </div><br/>
                        <div className="card">
                            <div className="card-header">Market Statistics</div>
                            <div className="card-body">
                                <MarketDeltaChart fromTime={this.state.fromTime} toTime={this.state.toTime} interval={this.state.interval} height={this.state.mdHeight} updateInterval={this.state.updateInterval} />
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
                        <OrderForm currentPrice={this.state.currentPrice} alert={this.alert.bind(this)} />
                    </div>
                </div>
            </div>
        } else {
            loginButton = <button type="button" className="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loginForm">Login</button>;
            content = <LoginForm onSuccess={this.onLoginSuccess.bind(this)} onFail={this.onLoginFail.bind(this)} />
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

    alert(message, type, title)
    {
        let self = this;
        this.setState({
            alert: {
                show: true,
                type: type,
                message: message,
                title: title,
            }
        });
        clearTimeout(this.alertTimeout);
        this.alertTimeout = setTimeout(function() {
            self.setState({alert:{show: false}});
        }, 3000);
    }

    onLoginSuccess(accessToken, userName)
    {
        this.alert('Login successfull');
        LoginHelper.login(accessToken, userName);
        this.setState({isLoggedIn: LoginHelper.isLoggedIn()});
    }

    onLoginFail(message)
    {
        this.alert(message, 'danger');
        this.setState({isLoggedIn: LoginHelper.isLoggedIn()});
    }

    onLogoutClick()
    {
        LoginHelper.logout();
        this.setState({isLoggedIn: LoginHelper.isLoggedIn()});
    }

    componentDidMount()
    {
        /*setInterval(function(){
            console.log('Refreshing market statistics...');
            self.refresh();
        }, this.props.updateInterval);*/
    }
}

if (document.getElementById('app')) {
    ReactDOM.render(<App/>, document.getElementById('app'));
}
