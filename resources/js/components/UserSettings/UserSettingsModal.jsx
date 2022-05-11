import React, {useRef, useContext} from 'react';
import RequestHelper from "../../Helpers/RequestHelper";
import $ from "jquery";
import {stateContext} from "../StateProvider";

function UserSettingsModal({showPopup}) {
    const binanceApiKeyRef = useRef();
    const binanceApiSeceretRef = useRef();
    const aoTpRef = useRef();
    const aoSlRef = useRef();
    const aoAmountRef = useRef();
    const aoLimitIndentPercentRef = useRef();
    const aoEnabledRef = useRef();
    const [state, actions] = useContext(stateContext);

    const onSaveClick = () => {
        let data = new FormData();
        data.append('binance_api_key', binanceApiKeyRef.current.value);
        data.append('binance_api_secret', binanceApiSeceretRef.current.value);
        data.append('ao_tp_percent', aoTpRef.current.value);
        data.append('ao_sl_percent', aoSlRef.current.value);
        data.append('ao_amount', aoAmountRef.current.value);
        data.append('ao_limit_indent_percent', aoLimitIndentPercentRef.current.value);
        data.append('ao_enabled', aoEnabledRef.current.checked ? 1 : 0);
        RequestHelper.fetch('/api/user', {
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

    const onFormFieldChange = (event, field) => {
        actions.setUser({...state.user, [field]: event.target.value})
    }

    const onFormCheckboxFieldChange = (event, field) => {
        actions.setUser({...state.user, [field]: event.target.checked})
    }

    return (
        <div className="modal fade" id="userSettingsModal" tabIndex="-1" aria-labelledby="userSettingsModalLabel" aria-hidden="true">
            <div className="modal-dialog">
                <div className="modal-content">
                    <div className="modal-header">
                        <h5 className="modal-title" id="userSettingsModalLabel">Settings</h5>
                        <button type="button" className="btn-close" data-bs-dismiss="modal" aria-label="Close"> </button>
                    </div>
                    <div className="modal-body">
                        <form>
                            <div className={"nav nav-tabs"}>
                                <button type={"button"} id={"nav-credentials-tab"} className={"nav-link active"} role={"tab"} data-bs-target={"#nav-credentials"} data-bs-toggle={"tab"} aria-controls={"nav-credentials"}>Binance</button>
                                <button type={"button"} id={"nav-ao-tab"} className={"nav-link"} role={"tab"} data-bs-target={"#nav-ao"} data-bs-toggle={"tab"} aria-controls={"nav-ao"}>Automatic orders</button>
                            </div>
                            <div className={"tab-content"} id={"settingsTabContent"}>
                                <div className={"tab-pane fade active show"} id={"nav-credentials"} role={"tab-panel"}>
                                    <div className="input-group input-group-sm mb-4">
                                        <label htmlFor="price" className="input-group-text w-25 bg-dark text-white">API Key</label>
                                        <input type="text" name="binance_api_key" id="binance_api_key" value={state.user.binance_api_key} onChange={event => onFormFieldChange(event, 'binance_api_key')} className="form-control bg-dark text-white" ref={binanceApiKeyRef} />
                                    </div>
                                    <div className="input-group input-group-sm mb-4">
                                        <label htmlFor="volume" className="input-group-text w-25 bg-dark text-white">API secret</label>
                                        <input type="text" name="binance_api_secret" id="binance_api_secret" value={state.user.binance_api_secret} onChange={event => onFormFieldChange(event, 'binance_api_secret')} className="form-control bg-dark text-white" ref={binanceApiSeceretRef} />
                                    </div>
                                </div>
                                <div className={"tab-pane fade"} id={"nav-ao"} role={"tab-panel"}>
                                    <div className="form-check form-switch mb-4">
                                        <input name={"ao_anabled"} className="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault" checked={state.user.ao_enabled} ref={aoEnabledRef} onChange={event => onFormCheckboxFieldChange(event, 'ao_enabled')} />
                                        <label className="form-check-label" htmlFor="flexSwitchCheckDefault">Enabled</label>
                                    </div>
                                    <div className="input-group input-group-sm mb-4">
                                        <label htmlFor="volume" className="input-group-text w-25 bg-dark text-white">Amount</label>
                                        <input type="text" name="ao_amount" id="ao_amount" value={state.user.ao_amount} onChange={event => onFormFieldChange(event, 'ao_amount')} className="form-control bg-dark text-white" ref={aoAmountRef} />
                                    </div>
                                    <div className="input-group input-group-sm mb-4">
                                        <label htmlFor="price" className="input-group-text w-25 bg-dark text-white">Take profit, %</label>
                                        <input type="text" name="ao_tp_percent" id="ao_tp_percent" value={state.user.ao_tp_percent} onChange={event => onFormFieldChange(event, 'ao_tp_percent')} className="form-control bg-dark text-white" ref={aoTpRef} />
                                    </div>
                                    <div className="input-group input-group-sm mb-4">
                                        <label htmlFor="volume" className="input-group-text w-25 bg-dark text-white">Stop loss, %</label>
                                        <input type="text" name="ao_sl_percent" id="ao_sl_percent" value={state.user.ao_sl_percent} onChange={event => onFormFieldChange(event, 'ao_sl_percent')} className="form-control bg-dark text-white" ref={aoSlRef} />
                                    </div>
                                    <div className="input-group input-group-sm mb-4">
                                        <label htmlFor="volume" className="input-group-text w-25 bg-dark text-white">Limit indent, %</label>
                                        <input type="text" name="ao_limit_indent_percent" id="ao_limit_indent_percent" value={state.user.ao_limit_indent_percent} onChange={event => onFormFieldChange(event, 'ao_limit_indent_percent')} className="form-control bg-dark text-white" ref={aoLimitIndentPercentRef} />
                                    </div>
                                </div>
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
