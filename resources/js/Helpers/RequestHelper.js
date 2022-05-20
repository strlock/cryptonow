import LoginHelper from "../Helpers/LoginHelper";

const syncFetch = require('sync-fetch');

class RequestHelper
{
    static expiredTokenCallback = null

    static fetch(url, options, success, failed)
    {
        if (options === undefined) {
            options = {}
        }
        const accessToken = LoginHelper.getAccessToken();
        if (accessToken) {
            options.headers = {
                'Authorization': 'Bearer ' + accessToken,
            };
        }
        return fetch(url, options).then(response => response.json()).then(function (response) {
            if (response.status !== undefined) {
                if (response.statusCode === 1 || response.statusCode === 2) {
                    LoginHelper.clearAccessToken();
                    if (RequestHelper.expiredTokenCallback !== null) {
                        RequestHelper.expiredTokenCallback();
                    }
                    if (failed) {
                        failed.call(this);
                        console.log('Token is expired!');
                    }
                }
                return;
            }
            if (success !== undefined) {
                success.call(this, response);
            }
        }).catch(function (error) {
            if (failed) {
                failed.call(this, error);
                console.log(error);
            }
        });
    }

    static async syncFetch(url, options) {
        if (options === undefined) {
            options = {}
        }
        const accessToken = LoginHelper.getAccessToken();
        if (accessToken) {
            options.headers = {
                'Authorization': 'Bearer ' + accessToken,
            };
        }
        const _response = await fetch(url, options);
        const response = await _response.json();
        if (response.status !== undefined && response.status === 'Token is Expired') {
            if (RequestHelper.expiredTokenCallback !== null) {
                RequestHelper.expiredTokenCallback();
            }
        }
        return response;
    }

    static setExpiredTokenCallback (callback)
    {
        RequestHelper.expiredTokenCallback = callback;
    }
}

export default RequestHelper;
