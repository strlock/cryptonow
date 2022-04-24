import React from 'react';
import { format } from "date-fns";

class MarketDeltaSeriesData extends React.Component {
    constructor(props) {
        super(props);
    }
    render() {
        return (
            <div>
                {this.props.data.map((point, i) => {
                    return (
                        <div key={i}>{point.x} {format(new Date(point.x), "HH:mm")}: <b>{point.y.toFixed(2)}</b> BTC</div>
                    );
                })}
            </div>
        );
    }
}

export default MarketDeltaSeriesData;
