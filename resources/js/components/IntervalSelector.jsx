import React from 'react';
import TimeIntervals from "../TimeIntervals";
import IntervalSelectorButton from "./IntervalSelectorButton";

const IntervalSelector = () => {
    return (
        <div>
            <IntervalSelectorButton interval={TimeIntervals.ONE_MINUTE}>1m</IntervalSelectorButton>&nbsp;
            <IntervalSelectorButton interval={TimeIntervals.FIVE_MINUTES}>5m</IntervalSelectorButton>&nbsp;
            <IntervalSelectorButton interval={TimeIntervals.FIFTEEN_MINUTES}>15m</IntervalSelectorButton>&nbsp;
            <IntervalSelectorButton interval={TimeIntervals.THIRTEEN_MINUTES}>30m</IntervalSelectorButton>&nbsp;
            <IntervalSelectorButton interval={TimeIntervals.ONE_HOUR}>1h</IntervalSelectorButton>&nbsp;
            <IntervalSelectorButton interval={TimeIntervals.FOUR_HOURS}>4h</IntervalSelectorButton>&nbsp;
            <IntervalSelectorButton interval={TimeIntervals.ONE_DAY}>1d</IntervalSelectorButton>
        </div>
    );
}

export default IntervalSelector;
