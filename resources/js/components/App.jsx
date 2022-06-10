import React, {useEffect, useContext, useRef} from "react";
import ReactDOM from 'react-dom';
import LoginForm from "./Login/LoginForm";
import FormatHelper from "../Helpers/FormatHelper";
import RequestHelper from "../Helpers/RequestHelper";
import Loading from "./Loading/Loading";
import {stateContext} from "./StateProvider";
import Dashboard from "./Pages/Dashboard/Dashboard";
import About from "./Pages/About/About"
import usePopup from "../hooks/usePopup"
import { Route, Routes, HashRouter as Router } from 'react-router-dom';
import StateProvider from "./StateProvider";

const App = () => {
    const [state, actions] = useContext(stateContext)
    const popup = usePopup()

    useEffect(() => {
        RequestHelper.setExpiredTokenCallback(() => {
            actions.setUser(null);
        })
        RequestHelper.fetch('/api/user', {}, response => {
            if (response.data !== undefined) {
                actions.setUser(response.data);
            }
            actions.setInitialized(true);
        }, () => {
            actions.setInitialized(true);
        });
    }, []);

    const onLoginSuccess = (user) => {
        actions.setUser(user);
    }

    const onLoginFail = (message) => {
        popup.show(message, 'danger');
    }

    FormatHelper.setFromSign('₿');
    FormatHelper.setToSign('$');
    if (state.initialized === false) {
        return ( <Loading /> )
    }
    return state.user !== null ? (
        <Routes>
            <Route path={"/about"} element={<About />} />
            <Route path={"/"} element={<Dashboard />} />
        </Routes>
    ) : <LoginForm onSuccess={onLoginSuccess} onFail={onLoginFail} />
}

if (document.getElementById('app')) {
    ReactDOM.render(
        <StateProvider>
            <Router>
                <App/>
            </Router>
        </StateProvider>,
        document.getElementById('app')
    );
}
