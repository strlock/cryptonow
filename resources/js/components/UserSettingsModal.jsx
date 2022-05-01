import React, {useState, useEffect, useRef} from 'react';
import RequestHelper from "../Helpers/RequestHelper";
import $ from "jquery";

function UserSettingsModal({showPopup}) {
    const binanceApiKeyRef = useRef();
    const binanceApiSeceretRef = useRef();

    const [settings, setSettings] = useState({'binance_api_key': '', 'binance_api_secret': ''});

    const onSaveClick = () => {
        let data = new FormData();
        data.append('binance_api_key', binanceApiKeyRef.current.value);
        data.append('binance_api_secret', binanceApiSeceretRef.current.value);
        RequestHelper.fetch('/api/user/settings', {
            method: 'POST',
            body: data,
        }, response => {
            if (response.error) {
                showPopup(response.message, 'danger');
            } else {
                showPopup(response.message);
            }
            $('#userSettingsModal .btn-close').trigger('click');
        });
    }

    useEffect(() => {
        RequestHelper.fetch('/api/user/settings', {}, response => {
            if (response.data !== undefined) {
                setSettings(response.data);
            }
        });
    }, []);

    const onBinanceApiKeyChange = event => {
        setSettings({...settings, binance_api_key: event.target.value})
    }

    const onBinanceApiSecretChange = event => {
        setSettings({...settings, binance_api_secret: event.target.value})
    }

    return (
        <div className="modal fade" id="userSettingsModal" tabIndex="-1" aria-labelledby="userSettingsModalLabel"
             aria-hidden="true">
            <div className="modal-dialog">
                <div className="modal-content">
                    <div className="modal-header">
                        <h5 className="modal-title" id="userSettingsModalLabel">Settings</h5>
                        <button type="button" className="btn-close" data-bs-dismiss="modal" aria-label="Close"> </button>
                    </div>
                    <div className="modal-body">
                        <form>
                            <div className="input-group input-group-sm mb-4">
                                <label htmlFor="price" className="input-group-text w-25 bg-dark text-white">API Key</label>
                                <input type="text" name="binance_api_key" id="binance_api_key" value={settings.binance_api_key} onChange={event => onBinanceApiKeyChange(event)} className="form-control bg-dark text-white" ref={binanceApiKeyRef} />
                            </div>
                            <div className="input-group input-group-sm mb-4">
                                <label htmlFor="volume" className="input-group-text w-25 bg-dark text-white">API secret</label>
                                <input type="text" name="binance_api_secret" id="binance_api_secret" value={settings.binance_api_secret} onChange={event => onBinanceApiSecretChange(event)} className="form-control bg-dark text-white" ref={binanceApiSeceretRef} />
                            </div>
                        </form>
                    </div>
                    <div className="modal-footer">
                        <button type="button" className="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" className="btn btn-primary" onClick={onSaveClick}>Save</button>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default UserSettingsModal;
