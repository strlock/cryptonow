import React from "react";
import RequestHelper from "../Helpers/RequestHelper";
import {ORDER_STATE_TITLES, ORDER_DIRECTION_TITLES, ORDERS_LIST_TAB_TITLES, ORDER_LIST_TAB_ORDER_SATES} from "../constants";
import FormatHelper from "../Helpers/FormatHelper";

class OrdersList extends React.Component
{
    state = {
        orders: [],
    }

    render()
    {
        const tabAliases = ['active', 'history'];
        return (
            <div className="card">
                <div className="card-header">Orders</div>
                <div className="card-body">
                    <div className="table-responsive orders">
                        <nav>
                            <div className="nav nav-tabs" id="nav-tab" role="tablist">
                                {tabAliases.map((tabAlias, i) => {
                                    let liClass = 'nav-link' + (i === 0 ? ' active' : '');
                                    let tabId = "nav-" + tabAlias + "-tab";
                                    let tabTarget = "#nav-" + tabAlias;
                                    let tabAriaControls = "nav-" + tabAlias;
                                    return (
                                        <button className={liClass} id={tabId} data-bs-toggle="tab"
                                                data-bs-target={tabTarget} type="button" role="tab" aria-controls={tabAriaControls}
                                                aria-selected={i === 0 ? "true" : "false"} key={tabAlias}>{ORDERS_LIST_TAB_TITLES[tabAlias]}</button>
                                    );
                                })}
                            </div>
                        </nav>
                        <div className="tab-content" id="myTabContent">
                            {tabAliases.map((tabAlias, i) => {
                                let paneClass = "tab-pane fade " + (i === 0 ? ' active show' : '');
                                let paneId = "nav-" + tabAlias;
                                return (
                                    <div className={paneClass} id={paneId} role="tabpanel" key={tabAlias}>
                                        <table className="table">
                                            <thead>
                                            <tr>
                                                <th className={"text-center"}>Date</th>
                                                <th className={"text-center"}>Asset</th>
                                                <th className={"text-center"}>Direction</th>
                                                <th className={"text-center"}>Price</th>
                                                <th className={"text-center"}>Amount</th>
                                                <th className={"text-center"}>Stop Loss/Take Profit</th>
                                                <th className={"text-center"}>Completion</th>
                                                <th className={"text-center"}>Status</th>
                                                <th></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            {this.state.orders.filter(order => ORDER_LIST_TAB_ORDER_SATES[tabAlias].indexOf(order.state) !== -1).map((order) => {
                                                let orderClass = 'order order-' + order.state;
                                                return (
                                                    <tr key={order.id} className={orderClass}>
                                                        <td className={"text-center order-created-at"}>{FormatHelper.formatDate(order.created_at)}</td>
                                                        <td className={"text-center order-symbol"}>{order.symbol}</td>
                                                        <td className={"text-center order-type"}>{ORDER_DIRECTION_TITLES[order.type]}</td>
                                                        <td className={"text-center order-price"}>{FormatHelper.formatPrice(order.price)}</td>
                                                        <td className={"text-center order-amount"}>{FormatHelper.formatAmount(order.amount)}</td>
                                                        <td className={"text-center order-sl-tp"}>
                                                            {FormatHelper.formatPrice(order.sl)}/{FormatHelper.formatPrice(order.tp)}<br/>
                                                            Ready: {FormatHelper.formatDate(order.ready_at)}{order.ready_price !== null ? '<br/>' + FormatHelper.formatPrice(order.ready_price) : ''}
                                                        </td>
                                                        <td className={"text-center order-completed-at"}>{FormatHelper.formatDate(order.completed_at)}{order.completed_price !== null ? '<br/>' + FormatHelper.formatPrice(order.completed_price) : ''}</td>
                                                        <td className={"text-center order-state"}>{ORDER_STATE_TITLES[order.state]}</td>
                                                        <td className={"text-center order-actions"}><button className="btn btn-danger btn-sm" onClick={() => this.onDeleteClick(order)}><i className="fa fa-times" aria-hidden="true"></i></button></td>
                                                    </tr>
                                                );
                                            } )}
                                            </tbody>
                                        </table>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    componentDidMount()
    {
        this.refresh();
    }

    refresh()
    {
        let self = this;
        return RequestHelper.fetch('/api/orders', {}, response => {
            self.setState({orders: response.data});
        });
    }

    onDeleteClick(order)
    {
        const self = this;
        RequestHelper.fetch('/api/orders/' + order.id, {
            method: 'DELETE',
        }, () => {
            self.refresh();
        });
    }
}

export default OrdersList;
