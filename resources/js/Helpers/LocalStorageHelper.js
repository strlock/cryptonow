class LocalStorageHelper
{
    static get(key)
    {
        let value = localStorage.getItem(key);
        return value && value != '' ? JSON.parse(value) : null;
    }

    static set(key, value)
    {
        localStorage.setItem(key, JSON.stringify(value));
    }

    static remove(key)
    {
        localStorage.removeItem(key);
    }
}

export default LocalStorageHelper;
