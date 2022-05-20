import {stateContext} from "./StateProvider";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faRightFromBracket } from "@fortawesome/free-solid-svg-icons";
import React, { useContext } from 'react';
import RequestHelper from "../Helpers/RequestHelper";
import LoginHelper from "../Helpers/LoginHelper";
import Alert from "react-bootstrap/Alert";
import UserSettingsModal from "./UserSettings/UserSettingsModal";
import usePopup from "../hooks/usePopup";
import {Link} from "react-router-dom"
import {faCircleQuestion, faChartColumn} from "@fortawesome/free-solid-svg-icons";

const Page = ({children}) => {
    const [state, actions] = useContext(stateContext)
    const popup = usePopup()

    const onLogoutClick = () => {
        RequestHelper.fetch('/api/logout', {method: 'POST'}, response => {
            if (response.success) {
                LoginHelper.clearAccessToken();
                actions.setUser(null);
            }
        });
    }

    const popupDom = <Alert variant={state.popup.type} onClose={() => popup.hide()} dismissible>
                         <Alert.Heading>{state.popup.title}</Alert.Heading>
                         <p>{state.popup.message}</p>
                     </Alert>;
    return (
        <div id="page">
            { state.user !== null ? (
                <div id="top">
                    <div className="top-left">
                        <a href="/" className="logo-link">
                            <img src="images/logo.png" />
                        </a>
                    </div>
                    <div className="top-right">
                        <button type="button" className="btn btn-success new-order-button" data-bs-toggle="modal" data-bs-target="#newOrderModal">New order</button>&nbsp;&nbsp;&nbsp;
                        <button type="button" className="btn btn-primary" onClick={() => onLogoutClick()}>{state.user.name}&nbsp;&nbsp;&nbsp;<FontAwesomeIcon icon={faRightFromBracket} /></button>&nbsp;&nbsp;&nbsp;
                        <button type="button" className="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#userSettingsModal"><i className="fa fa-gear"></i></button>
                    </div>
                </div>
            ) : null}
            <div id="middle">
                <nav id={"left-nav"}>
                    <Link to="/" className={"left-nav-link"}><FontAwesomeIcon icon={faChartColumn} /></Link>&nbsp;
                    <Link to="/about" className={"left-nav-link"}><FontAwesomeIcon icon={faCircleQuestion} /></Link>
                </nav>
                <div className="container">
                    <div className="row justify-content-center">
                        <div className="col-xl-12">
                            {state.popup.show ? popupDom : ''}
                        </div>
                        <div className="col-xl-12">
                            {children}
                            <UserSettingsModal />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default Page
