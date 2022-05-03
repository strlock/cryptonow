import React, {useState, useEffect, useContext} from "react";
import RequestHelper from "../Helpers/RequestHelper";
import {ORDER_STATE_TITLES, ORDER_DIRECTION_TITLES, ORDERS_LIST_TAB_TITLES, ORDER_LIST_TAB_ORDER_SATES} from "../constants";
import FormatHelper from "../Helpers/FormatHelper";
import $ from "jquery";
import ordersContext from "../contexts/OrdersContext";

const OrdersList = () => {
    const orders = useContext(ordersContext);
    const tabAliases = ['active', 'history'];

    const onDeleteClick = (order) => {
        const $button = $('#order-delete-button-' + order.id);
        $button.addClass('spinner-border');
        RequestHelper.fetch('/api/orders/' + order.id, {
            method: 'DELETE',
        }, () => {
            refresh();
        });
    }

    return (
        <div className="card">
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
                            const paneClass = "tab-pane fade " + (i === 0 ? ' active show' : '');
                            const paneId = "nav-" + tabAlias;
                            const tabOrders = orders.filter(order => ORDER_LIST_TAB_ORDER_SATES[tabAlias].indexOf(order.state) !== -1);
                            return (
                                <div className={paneClass} id={paneId} role="tabpanel" key={tabAlias}>
                                    {tabOrders.length > 0
                                        ? (
                                            <table className="table">
                                                <thead>
                                                <tr>
                                                    <th className={"text-start"}>Order</th>
                                                    <th className={"text-center"}>Price</th>
                                                    <th className={"text-center"}>Stop Loss</th>
                                                    <th className={"text-center"}>Take Profit</th>
                                                    <th className={"text-center"}>Status</th>
                                                    <th className={"text-center"} width={50}>ID</th>
                                                    <th className={"text-end"} width={50}></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                {tabOrders.map((order) => {
                                                    const orderClass = 'order order-' + order.state;
                                                    return (
                                                        <tr key={order.id} className={orderClass}>
                                                            <td className={"text-start order-symbol"}>{ORDER_DIRECTION_TITLES[order.direction]} {FormatHelper.formatAmount(order.amount, true)}</td>
                                                            <td className={"text-center order-price"}>{FormatHelper.formatPrice(order.price, true)}</td>
                                                            <td className={"text-center order-sl-tp"}>{FormatHelper.formatPrice(order.sl, true)}</td>
                                                            <td className={"text-center order-sl-tp"}>{FormatHelper.formatPrice(order.tp, true)}</td>
                                                            <td className={"text-center order-state"}>{ORDER_STATE_TITLES[order.state]}</td>
                                                            <td className={"text-center order-symbol"}>{order.id}</td>
                                                            <td className={"text-end order-actions"}>
                                                                {tabAlias === 'active' ? <button className="btn btn-danger btn-delete btn-sm" id={'order-delete-button-' + order.id} onClick={() => onDeleteClick(order)}><i className="fa fa-times" aria-hidden="true"> </i></button> : ''}
                                                            </td>
                                                        </tr>
                                                    );
                                                })}
                                                </tbody>
                                            </table>
                                        ) : ( <div className={"text-center"}>No orders</div> )
                                    }
                                </div>
                            );
                        })}
                    </div>
                </div>
            </div>
        </div>
    );
}

export default OrdersList;
