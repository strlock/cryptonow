import LocalStorageHelper from "./LocalStorageHelper";

class LoginHelper
{
    static setAccessToken(accessToken, userName)
    {
        LocalStorageHelper.set('userAccessToken', accessToken);
    }

    static getAccessToken()
    {
        return LocalStorageHelper.get('userAccessToken');
    }

    static clearAccessToken()
    {
        LocalStorageHelper.remove('userAccessToken');
    }
}

export default LoginHelper;
