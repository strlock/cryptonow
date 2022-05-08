import React from 'react';
import TimeIntervals from "../TimeIntervals";
import IntervalSelectorButton from "./IntervalSelectorButton";

const IntervalSelector = ({chartsInterval, setChartsInterval}) => {
    return (
        <div>
            <IntervalSelectorButton setChartsInterval={setChartsInterval} interval={TimeIntervals.ONE_MINUTE} currentInterval={chartsInterval}>1m</IntervalSelectorButton>&nbsp;
            <IntervalSelectorButton setChartsInterval={setChartsInterval} interval={TimeIntervals.FIVE_MINUTES} currentInterval={chartsInterval}>5m</IntervalSelectorButton>&nbsp;
            <IntervalSelectorButton setChartsInterval={setChartsInterval} interval={TimeIntervals.FIFTEEN_MINUTES} currentInterval={chartsInterval}>15m</IntervalSelectorButton>&nbsp;
            <IntervalSelectorButton setChartsInterval={setChartsInterval} interval={TimeIntervals.THIRTEEN_MINUTES} currentInterval={chartsInterval}>30m</IntervalSelectorButton>&nbsp;
            <IntervalSelectorButton setChartsInterval={setChartsInterval} interval={TimeIntervals.ONE_HOUR} currentInterval={chartsInterval}>1h</IntervalSelectorButton>&nbsp;
            <IntervalSelectorButton setChartsInterval={setChartsInterval} interval={TimeIntervals.FOUR_HOURS} currentInterval={chartsInterval}>4h</IntervalSelectorButton>&nbsp;
            <IntervalSelectorButton setChartsInterval={setChartsInterval} interval={TimeIntervals.ONE_DAY} currentInterval={chartsInterval}>1d</IntervalSelectorButton>
        </div>
    );
}

export default IntervalSelector;
