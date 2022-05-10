import React, {useState,useContext, useEffect, useMemo} from 'react';
import ReactApexChart from 'react-apexcharts';
import RequestHelper from "../Helpers/RequestHelper";
import FormatHelper from "../Helpers/FormatHelper";
import {stateContext} from "./StateProvider";

let chartContext = null;

const PriceChart = ({fromTime, toTime, interval, height, textColor, linesColor, xAnnotations, yAnnotations, orders}) => {
    const [seriesData, setSeriesData] = useState([]);
    const [state, actions] = useContext(stateContext);

    useEffect(() => {
        RequestHelper.fetch('/api/price/BTCUSD/' + fromTime + '/' + toTime + '/' + interval, {},
            response => {
                setSeriesData(response.data);
            },
            error => console.log(error)
        );
    }, [fromTime, toTime, interval]);

    const priceAnnotation = useMemo(() => {
        return {
            y: state.currentPrice,
            borderColor: '#fff',
            strokeDashArray: 1,
            label: {
                borderColor: linesColor,
                position: 'right',
                textAnchor: 'end',
                style: {
                    color: textColor,
                    background: 'transparent'
                },
                text: FormatHelper.formatPrice(state.currentPrice)
            }
        };
    }, [state.currentPrice]);

    const yRange = useMemo(() => {
        let prices = [];
        const addPrice = (price) => {
            price = parseFloat(price)
            if (!isNaN(price) && price !== 0.0 && prices.indexOf(price) === -1) {
                prices.push(price);
            }
        }
        orders.forEach((order) => {
            addPrice(order.price);
            addPrice(order.sl);
            addPrice(order.tp);
        });
        seriesData.forEach((item) => {
            addPrice(item.y[1]);
            addPrice(item.y[2]);
        });
        return {min: Math.min(...prices), max: Math.max(...prices)};
    }, [orders, seriesData]);

    const options = {
        chart: {
            type: 'candlestick',
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
                },
            },
        },
        dataLabels: {
            enabled: false,
        },
        yaxis: {
            title: {
                text: 'USDT',
                align: 'center',
                style: {
                    fontWeight: 'bold',
                    color: textColor,
                },
            },
            labels: {
                minWidth: 80,
                formatter: function (y) {
                    return y + ' USDT';
                },
                style: {
                    colors: textColor,
                }
            },
            forceNiceScale: true,
            min: yRange.min,
            max: yRange.max,
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
            palette: 'palette2',
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
            position: 'front',
            yaxis: [...yAnnotations, priceAnnotation],
            xaxis: xAnnotations,
        }
    }

    const series = [{name: 'Price', data: seriesData}];

    return (
        <ReactApexChart options={options} series={series} type="candlestick" height={height} />
    );
};

export default PriceChart;
