import React, {useState,useContext, useEffect, useMemo} from 'react';
import ReactApexChart from 'react-apexcharts';
import RequestHelper from "../Helpers/RequestHelper";
import FormatHelper from "../Helpers/FormatHelper";
import CurrentPrice from "./CurrentPrice";
import ordersContext from "../contexts/OrdersContext";
import {
    ORDER_DIRECTION_BUY
} from "../constants";

let chartContext = null;

const PriceChart = ({fromTime, toTime, interval, height, textColor, linesColor}) => {
    const orders = useContext(ordersContext);
    const [seriesData, setSeriesData] = useState([]);

    useEffect(() => {
        RequestHelper.fetch('/api/price/BTCUSDT/' + fromTime + '/' + toTime + '/' + interval, {},
            response => {
                setSeriesData(response.data);
            },
            error => console.log(error)
        );
    }, [fromTime, toTime, interval]);

    const yAnnotations = useMemo(() => {
        const result = [];
        const filteredOrders = orders.filter((order) => {
            return order.state === 'new' || order.state === 'ready';
        });
        for(let i in filteredOrders) {
            const order = filteredOrders[i];
            result.push({
                y: order.price,
                borderColor: order.direction === ORDER_DIRECTION_BUY ? '#00E396' : '#E30096',
                strokeDashArray: 0,
                label: {
                    borderColor: linesColor,
                    style: {
                        color: textColor,
                        background: 'transparent'
                    },
                    text: 'Order ' + order.id + ': ' + FormatHelper.formatPrice(order.price)
                }
            });
            if (order.sl) {
                result.push({
                    y: order.sl,
                    borderColor: order.direction === ORDER_DIRECTION_BUY ? '#E30096' : '#00E396',
                    strokeDashArray: 5,
                    label: {
                        borderColor: linesColor,
                        style: {
                            color: textColor,
                            background: 'transparent'
                        },
                        text: 'Order ' + order.id + ' SL: ' + FormatHelper.formatPrice(order.sl)
                    }
                });
            }
            if (order.tp) {
                result.push({
                    y: order.tp,
                    borderColor: order.direction === ORDER_DIRECTION_BUY ? '#E30096' : '#00E396',
                    strokeDashArray: 5,
                    label: {
                        borderColor: linesColor,
                        style: {
                            color: textColor,
                            background: 'transparent'
                        },
                        text: 'Order ' + order.id + ' TP: ' + FormatHelper.formatPrice(order.tp)
                    }
                });
            }
        }
        return result;
    }, [orders]);

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
                formatter: function (y) {
                    return y + ' USDT';
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
            yaxis: yAnnotations,
        }
    }

    const series = [{name: 'Price', data: seriesData}];

    return (
        <div className="card">
            <div className="card-header">Price<CurrentPrice symbol={"BTCBUSD"} /></div>
            <div className="card-body pt-0">
                <div className="chart">
                    <ReactApexChart options={options} series={series} type="candlestick" height={height} />
                </div>
            </div>
        </div>
    );
};

export default PriceChart;
