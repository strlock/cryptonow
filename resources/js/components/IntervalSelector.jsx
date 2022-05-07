import React from 'react';
import TimeIntervals from "../TimeIntervals";

const IntervalSelector = (props) => {
    const onChangeChartsInterval = (newInterval) => {
        props.setChartsInterval(newInterval);
    }

    return (
        <div>
            <button onClick={() => onChangeChartsInterval(TimeIntervals.ONE_MINUTE)} className="btn btn-primary btn-sm">1m</button>&nbsp;
            <button onClick={() => onChangeChartsInterval(TimeIntervals.FIVE_MINUTES)} className="btn btn-secondary btn-sm">5m</button>&nbsp;
            <button onClick={() => onChangeChartsInterval(TimeIntervals.FIFTEEN_MINUTES)} className="btn btn-primary btn-sm">15m</button>&nbsp;
            <button onClick={() => onChangeChartsInterval(TimeIntervals.THIRTEEN_MINUTES)} className="btn btn-secondary btn-sm">30m</button>&nbsp;
            <button onClick={() => onChangeChartsInterval(TimeIntervals.ONE_HOUR)} className="btn btn-primary btn-sm">1h</button>&nbsp;
            <button onClick={() => onChangeChartsInterval(TimeIntervals.FOUR_HOURS)} className="btn btn-secondary btn-sm">4h</button>&nbsp;
            <button onClick={() => onChangeChartsInterval(TimeIntervals.ONE_DAY)} className="btn btn-primary btn-sm">1d</button>
        </div>
    );
}

export default IntervalSelector;
