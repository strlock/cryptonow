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
            return order;
        });
        return (
            <div className="table-responsive orders">
                <table className="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Price</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Stop Loss</th>
                            <th>Take Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        {orders.map((order) => {
                            let orderClass = 'order order-' + order.state;
                            return (
                                <tr key={order.id} className={orderClass}>
                                    <td>{order.id}</td>
                                    <td>{order.created_at_formatted}</td>
                                    <td>{parseFloat(order.price).toFixed(2)}</td>
                                    <td>{parseFloat(order.amount).toFixed(5)}</td>
                                    <td>{order.type}</td>
                                    <td>{order.sl}</td>
                                    <td>{order.tp}</td>
                                </tr>
                            );
                        } )}
                    </tbody>
                </table>
            </div>
        );
    }

    componentDidMount()
    {
        let self = this;
        return RequestHelper.fetch('/api/orders', {
            headers: {
                'Authorization': 'Bearer ' + LoginHelper.getAccessToken(),
            },
        }, function(response) {
            self.setState({orders: response.data});
        });
    }
}

export default OrdersList;
