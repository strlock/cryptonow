import React from "react";
import LoginHelper from "../Helpers/LoginHelper";
import RequestHelper from "../Helpers/RequestHelper";

class OrdersList extends React.Component
{
    state = {
        orders: [],
    }

    render()
    {
        const orders = this.state.orders.map(function (order) {
            order.created_at_formatted = (new Date(order.created_at)).toLocaleString();
            order.ready_at_formatted = order.ready_at ? (new Date(order.ready_at)).toLocaleString() : '-';
            order.completed_at_formatted = order.completed_at ? (new Date(order.completed_at)).toLocaleString() : '-';
            return order;
        });
        return (
            <div className="card">
                <div className="card-header">Orders</div>
                <div className="card-body">
                    <div className="table-responsive orders">
                        <table className="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Price</th>
                                    <th>Amount</th>
                                    <th>Type</th>
                                    <th>Stop Loss/Take Profit</th>
                                    <th>Buy/Sell</th>
                                    <th>Completion</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                {orders.map((order) => {
                                    let orderClass = 'order order-' + order.state;
                                    return (
                                        <tr key={order.id} className={orderClass}>
                                            <td>{order.id}</td>
                                            <td>{order.created_at_formatted}</td>
                                            <td>{parseFloat(order.price).toFixed(2)}/</td>
                                            <td>{parseFloat(order.amount).toFixed(5)}</td>
                                            <td>{order.type}</td>
                                            <td>{parseFloat(order.sl).toFixed(2)}/{parseFloat(order.tp).toFixed(2)}</td>
                                            <td>{order.ready_at_formatted}{order.ready_price ? ', ' + parseFloat(order.ready_price).toFixed(2) : ''}</td>
                                            <td>{order.completed_at_formatted}{order.completed_price ? ', ' + parseFloat(order.completed_price).toFixed(2) : ''}</td>
                                            <td><button className="btn btn-danger btn-sm" onClick={() => this.onDeleteClick(order)}><i className="fa fa-times" aria-hidden="true"></i></button></td>
                                        </tr>
                                    );
                                } )}
                            </tbody>
                        </table>
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
