import React, {useState, useEffect, useMemo, useContext} from 'react';
import ReactApexChart from 'react-apexcharts';
import RequestHelper from "../Helpers/RequestHelper";
import {StrategySignals} from "../constants";

let chartContext = null

const MarketDeltaChart = ({fromTime, toTime, interval, linesColor, textColor, height, xAnnotations}) => {
    const [seriesData, setSeriesData] = useState([]);

    const options = {
        chart: {
            type: 'bar',
            selection: {
                enabled: false,
            },
            sparkline: {
                enabled: false,
            },
            zoom: {
                enabled: false,
            },
            animations: {
                enabled: false,
            },
            events: {
                mounted: function (cc, config) {
                    chartContext = cc;
                }
            },
        },
        plotOptions: {
            bar: {
                colors: {
                    ranges: [{
                        from: -999999,
                        to: 0,
                        color: 'rgba(239,64,60,1)'
                    }, {
                        from: 0,
                        to: 999999,
                        color: 'rgba(0,183,70,1)'
                    }]
                },
                columnWidth: '80%',
            }
        },
        dataLabels: {
            enabled: false,
        },
        yaxis: {
            title: {
                text: 'BTC',
                align: 'center',
                style: {
                    fontWeight: 'bold',
                    color: textColor,
                },
            },
            labels: {
                minWidth: 80,
                formatter: function (y) {
                    return y + ' BTC';
                },
                style: {
                    colors: textColor,
                }
            },
            forceNiceScale: true,
        },
        xaxis: {
            type: 'datetime',
            categories: [],
            tickAmount: 30,
            tickPlacement: 'on',
            labels: {
                format: 'HH:mm',
                rotate: -30,
                rotateAlways: true,
                hideOverlappingLabels: false,
                datetimeUTC: false,
                minHeight: 50,
                offsetY: 10,
                style: {
                    colors: textColor,
                },
                formatter: function (value, timestamp, opts) {
                    return opts.dateFormatter(new Date(timestamp), 'HH:mm')
                },
            },
            axisTicks: {
                show: true,
                borderType: 'solid',
                color: linesColor,
                height: 6,
                offsetX: 0,
                offsetY: 0
            },
            tooltip: {
                enabled: false
            },
            axisBorder: {
                show: true,
                color: linesColor,
            },
        },
        theme: {
            mode: 'dark',
            palette: 'palette1',
        },
        noData: {
            text: "Loading...",
            align: 'center',
            verticalAlign: 'middle',
            offsetX: 0,
            offsetY: 0,
            style: {
                color: textColor,
                fontSize: '14px',
                fontFamily: "Helvetica"
            }
        },
        grid: {
            borderColor: linesColor,
        },
        annotations: {
            xaxis: xAnnotations,
        }
    };

    const refresh = () => {
        RequestHelper.fetch('/api/marketDelta/BTCUSDT/' + fromTime + '/' + toTime + '/' + interval, {}, response => {
            setSeriesData(response.data);
        }, error => console.log(error));
    }

    useEffect(() => {
        refresh();
    }, [fromTime, toTime, interval]);

    useEffect(() => {
        setInterval(() => {
            refresh();
        }, 15000);
    }, []);

    const series = [{
        name: 'Market Statistics',
        data: seriesData,
    }];

    return (
        <ReactApexChart options={options} series={series} type="bar" height={height} />
    );
};

export default MarketDeltaChart;
