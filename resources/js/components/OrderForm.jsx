import React from "react";
import RequestHelper from "../Helpers/RequestHelper";
import FormatHelper from "../Helpers/FormatHelper";

class OrderForm extends React.Component
{
    state = {
        market: false,
        virtual: true,
        sl: 0,
        tp: 0,
        slPercent: 0,
        tpPercent: 0,
    }

    totalRef = null
    amountRef = null

    constructor() {
        super();
        this.totalRef = React.createRef();
        this.amountRef = React.createRef();
        this.priceRef = React.createRef();
        this.slRef = React.createRef();
        this.tpRef = React.createRef();
        this.marketRef = React.createRef();

        this.onSlChange = this.onSlChange.bind(this);
        this.onTpChange = this.onTpChange.bind(this);
    }

    render()
    {
        let self = this;
        this.onMarketChange = function(event) {
            const target = event.target;
            const value = target.type === 'checkbox' ? target.checked : target.value;
            self.setState({
                market: value
            });
        };
        return (
            <div className="card bg-secondary text-white mb-3">
                <div className="card-body">
                    <form>
                        <div className="input-group input-group-sm mb-4">
                            <label htmlFor="price" className="input-group-text w-25">Price</label>
                            <input type="number" name="price" id="price" defaultValue={this.state.market ? this.props.currentPrice : ''} className="form-control" step="0.01" disabled={this.state.market} ref={this.priceRef} onChange={() => this.onPriceChange()} />
                        </div>
                        <div className="input-group input-group-sm mb-4">
                            <label htmlFor="volume" className="input-group-text w-25">Amount</label>
                            <input type="number" name="amount" id="amount" className="form-control" step="0.00001" onChange={() => this.onAmountChange()} ref={this.amountRef} />
                        </div>
                        <div className="input-group input-group-sm mb-4">
                            <label htmlFor="total" className="input-group-text w-25">Total</label>
                            <input type="number" name="total" id="total" className="form-control" step="0.01" onChange={() => this.onTotalChange()} ref={this.totalRef} />
                        </div>
                        <div className="input-group input-group-sm mb-3">
                            <label htmlFor="sl" className="input-group-text w-25">Stop Loss</label>
                            <input type="number" name="sl" id="sl" className="form-control" step="0.01" value={this.state.sl} onChange={event => this.onSlChange(event)} ref={this.slRef} />
                            <input type="range" className="form-range mt-1" min="0" max="100" step="1" id="slRange" value={this.state.slPercent} onChange={event => this.onSlRangeChange(event)} />
                            <span>{this.state.slPercent}%</span>
                        </div>
                        <div className="input-group input-group-sm mb-3">
                            <label htmlFor="tp" className="input-group-text w-25">Take Profit</label>
                            <input type="number" name="tp" id="tp" className="form-control" step="0.01" value={this.state.tp} onChange={event => this.onTpChange(event)} ref={this.tpRef} />
                            <input type="range" className="form-range mt-1" min="0" max="100" step="1" id="tpRange" value={this.state.tpPercent} onChange={event => this.onTpRangeChange(event)} />
                            <span>{this.state.tpPercent}%</span>
                        </div>
                        <div className="form-check form-check-inline mb-3">
                            <input className="form-check-input" type="checkbox" id="marketCheckbox" defaultChecked={this.state.market} onClick={this.onMarketChange} ref={this.marketRef} />
                            <label className="form-check-label" htmlFor="marketCheckbox">Market</label>
                        </div>
                        <div className="form-check form-check-inline mb-3">
                            <input className="form-check-input" type="checkbox" id="virtualCheckbox" defaultChecked={this.state.virtual} onClick={this.onVirtualChange} ref={this.virtualRef} disabled={true} />
                            <label className="form-check-label" htmlFor="virtualCheckbox">Virtual</label>
                        </div>
                        <div className="input-group input-group-sm">
                            <button type="button" name="volume" className="btn btn-success form-control" onClick={() => this.onBuyClick()}>BUY</button>
                            <button type="button" name="volume" className="btn btn-danger form-control" onClick={() => this.onSellClick()}>SELL</button>
                        </div>
                    </form>
                </div>
            </div>
        );
    }

    onAmountChange()
    {
        let price = this.getFloatValueByRef(this.priceRef);
        let amount = this.getFloatValueByRef(this.amountRef);
        this.setFloatValueByRef(this.totalRef, price*amount);
    }

    onTotalChange()
    {
        let price = this.getFloatValueByRef(this.priceRef);
        let total = this.getFloatValueByRef(this.totalRef);
        this.setFloatValueByRef(this.amountRef, total/price, 5);
    }

    onPriceChange()
    {
        let price = this.getFloatValueByRef(this.priceRef);
        let amount = this.getFloatValueByRef(this.amountRef);
        this.setFloatValueByRef(this.totalRef, price*amount);
    }

    getFloatValueByRef(ref)
    {
        let result = ref.current.value.replace(/\,+/, '.');
        return !isNaN(result) ? result : 0.0;
    }

    setFloatValueByRef(ref, value, digits)
    {
        if (!digits) {
            digits = 2;
        }
        ref.current.value = value.toFixed(digits);
    }

    onBuyClick()
    {
        this.postOrder('buy');
    }

    onSellClick()
    {
        this.postOrder('sell');
    }

    postOrder(type)
    {
        let self = this;
        let data = new FormData();
        data.append('price', this.priceRef.current.value);
        data.append('amount', this.amountRef.current.value);
        data.append('sl', this.slRef.current.value);
        data.append('tp', this.tpRef.current.value);
        data.append('market', 1*this.marketRef.current.checked);
        data.append('type', type);
        data.append('exchange', 'binance');
        data.append('symbol', 'BTCUSDT');
        RequestHelper.fetch('/api/orders', {
            method: 'POST',
            body: data,
        }, response => {
            self.clearForm();
            self.props.showPopup('Order created!');
            this.props.ordersList.refresh();
        });
    }

    clearForm()
    {
        this.amountRef.current.value = '';
        this.totalRef.current.value = '';
        this.slRef.current.value = '';
        this.tpRef.current.value = '';
        this.marketRef.current.checked = false;
        this.virtualRef.current.checked = true;
    }

    onSlRangeChange(event)
    {
        const price = this.priceRef.current.value;
        const value = event.target.value;
        const sl = price*(1-value/100);
        console.log(FormatHelper.formatPrice(sl, false, ''));
        this.setState({
            sl: FormatHelper.formatPrice(sl, false, ''),
            slPercent: value,
        });
    }

    onTpRangeChange(event)
    {
        const price = this.priceRef.current.value;
        const value = event.target.value;
        const tp = price*(1+value/100);
        this.setState({
            tp: FormatHelper.formatPrice(tp, false, ''),
            tpPercent: value,
        });
    }

    onSlChange(event)
    {

    }

    onTpChange(event)
    {

    }
}

export default OrderForm;
