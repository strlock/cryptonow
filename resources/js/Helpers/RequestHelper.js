import LoginHelper from "../Helpers/LoginHelper";

const syncFetch = require('sync-fetch');

class RequestHelper
{
    static expiredTokenCallback = null

    static fetch(url, options, success, failed)
    {
        const accessToken = LoginHelper.getAccessToken();
        if (accessToken) {
            options.headers = {
                'Authorization': 'Bearer ' + accessToken,
            };
        }
        return fetch(url, options).then(response => response.json()).then(function (response) {
            if (response.status !== undefined && response.status === 'Token is Expired') {
                if (RequestHelper.expiredTokenCallback !== null) {
                    RequestHelper.expiredTokenCallback();
                }
                return;
            }
            if (success) {
                success.call(this, response);
            }
        }).catch(function (error) {
            if (failed) {
                failed.call(this, error);
            }
        });
    }

    static syncFetch(url, options) {
        return syncFetch(url, options).json();
    }

    static setExpiredTokenCallback (callback)
    {
        RequestHelper.expiredTokenCallback = callback;
    }
}

export default RequestHelper;
