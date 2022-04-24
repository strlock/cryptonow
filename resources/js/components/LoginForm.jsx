import React from 'react';
import $ from "jquery";
import RequestHelper from "../Helpers/RequestHelper";

class LoginForm extends React.Component {
    constructor(props)
    {
        super(props);
        this.emailRef = React.createRef();
        this.passwordRef = React.createRef();
    }

    render() {
        return (
            <div className="modal bg-secondary show" id="loginForm" tabIndex="-1" aria-labelledby="loginForm" aria-hidden="true" onClick={(event) => event.stopPropagation()}>
                <div className="modal-dialog">
                    <div className="modal-content">
                        <div className="modal-header">
                            <h5 className="modal-title">Login</h5>
                            <button type="button" className="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div className="modal-body p-2">
                            <div className="form-group mb-2">
                                <input type="email" name="email" className="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Email" ref={this.emailRef}/>
                            </div>
                            <div className="mb-2 form-group">
                                <input type="password" name="password" className="form-control" id="exampleInputPass1" aria-describedby="passHelp" placeholder="Password" ref={this.passwordRef}/>
                            </div>
                        </div>
                        <div className="modal-footer">
                            <button type="button" className="btn btn-primary form-control" onClick={this.login.bind(this)}>Login</button>
                            <button type="button" className="btn btn-secondary form-control" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    login() {
        let self = this;
        const data = new FormData();
        data.append('email', this.emailRef.current.value);
        data.append('password', this.passwordRef.current.value);
        RequestHelper.fetch('/api/login', {
            method: 'POST',
            body: data
        }, function (response) {
            if (typeof response.error != 'undefined') {
                if (typeof self.props.onFail != 'undefined') {
                    self.props.onFail(response.error);
                }
            } else if (self.props.onSuccess) {
                self.props.onSuccess(response.access_token, response.user_name);
            }
            $('#loginForm .btn-close').trigger('click');
        });
        return false;
    }
}

export default LoginForm;
