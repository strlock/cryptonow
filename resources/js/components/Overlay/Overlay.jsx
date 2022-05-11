import React from 'react';

class LoginForm extends React.Component {
    render() {
        return (
            <div className="overlay" onClick={this.props.onClick}>{this.props.children}</div>
        );
    }
}

export default LoginForm;
