import React from 'react';

function UserSettingsModal(props) {
    return (
        <div className="modal fade" id="userSettingsModal" tabIndex="-1" aria-labelledby="userSettingsModalLabel"
             aria-hidden="true">
            <div className="modal-dialog">
                <div className="modal-content">
                    <div className="modal-header">
                        <h5 className="modal-title" id="userSettingsModalLabel">Settings</h5>
                        <button type="button" className="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div className="modal-body">
                        <form>
                            <div className="input-group input-group-sm mb-4">
                                <label htmlFor="price" className="input-group-text w-25 bg-dark text-white">API Key</label>
                                <input type="text" name="api_key" id="api_key" defaultValue={""} className="form-control bg-dark text-white" />
                            </div>
                            <div className="input-group input-group-sm mb-4">
                                <label htmlFor="volume" className="input-group-text w-25 bg-dark text-white">API secret</label>
                                <input type="text" name="api_secret" id="api_secret" className="form-control bg-dark text-white" />
                            </div>
                        </form>
                    </div>
                    <div className="modal-footer">
                        <button type="button" className="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" className="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default UserSettingsModal;
