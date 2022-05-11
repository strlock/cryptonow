import React, {useRef, useContext} from 'react';
import $ from "jquery";
import RequestHelper from "../../Helpers/RequestHelper";
import LoginHelper from "../../Helpers/LoginHelper";

const LoginForm = ({onFail, onSuccess}) => {
    const emailRef = useRef();
    const passwordRef = useRef();

    const login = () => {
        const data = new FormData();
        data.append('email', emailRef.current.value);
        data.append('password', passwordRef.current.value);
        RequestHelper.fetch('/api/login', {
            method: 'POST',
            body: data
        }, response => {
            if (typeof response.error != 'undefined') {
                if (typeof onFail != 'undefined') {
                    onFail(response.error);
                }
            } else if (onSuccess) {
                onSuccess(response.user);
                LoginHelper.setAccessToken(response.access_token);
            }
            $('#loginForm .btn-close').trigger('click');
        });
        return false;
    }

    return (
        <div className="modal bg-secondary show" id="loginForm" tabIndex="-1" aria-labelledby="loginForm" aria-hidden="true" onClick={(event) => event.stopPropagation()}>
            <div className="modal-dialog">
                <div className="modal-content">
                    <div className="modal-header">
                        <h5 className="modal-title">Login</h5>
                    </div>
                    <div className="modal-body p-3">
                        <div className="form-group mb-3">
                            <input type="email" name="email" className="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Email" ref={emailRef}/>
                        </div>
                        <div className="mb-2 form-group">
                            <input type="password" name="password" className="form-control" id="exampleInputPass1" aria-describedby="passHelp" placeholder="Password" ref={passwordRef}/>
                        </div>
                    </div>
                    <div className="modal-footer">
                        <button type="button" className="btn btn-primary form-control" onClick={() => login()}>Login</button>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default LoginForm;
