import LocalStorageHelper from "./LocalStorageHelper";

class LoginHelper
{
    static login(accessToken, userName)
    {
        LocalStorageHelper.set('userAccessToken', accessToken);
        LocalStorageHelper.set('userName', userName);
    }

    static getAccessToken()
    {
        return LocalStorageHelper.get('userAccessToken');
    }

    static isLoggedIn()
    {
        let accessToken = this.getAccessToken();
        return accessToken !== null && accessToken != '';
    }

    static getLoggedInUserName()
    {
        return LocalStorageHelper.get('userName');
    }

    static logout()
    {
        LocalStorageHelper.remove('userAccessToken');
        LocalStorageHelper.remove('userName');
        document.location.reload();
    }
}

export default LoginHelper;
