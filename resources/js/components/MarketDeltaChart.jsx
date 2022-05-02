import React from 'react';
import ReactApexChart from 'react-apexcharts';
import RequestHelper from "../Helpers/RequestHelper";

class MarketDeltaChart extends React.Component {
    state = {
        series: [{
            name: 'Market Statistics',
            data: [],
        }],
        options: {
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
            },
            plotOptions: {
                bar: {
                    colors: {
                        ranges: [{
                            from: -1000,
                            to: 0,
                            color: 'rgba(239,64,60,1)'
                        }, {
                            from: 0,
                            to: 1000,
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
                        color: this.props.textColor,
                    },
                },
                labels: {
                    formatter: function (y) {
                        return y + ' BTC';
                    },
                    style: {
                        colors: this.props.textColor,
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
                        colors: this.props.textColor,
                    },
                    formatter: function (value, timestamp, opts) {
                        return opts.dateFormatter(new Date(timestamp), 'HH:mm')
                    },
                },
                axisTicks: {
                    show: true,
                    borderType: 'solid',
                    color: this.props.linesColor,
                    height: 6,
                    offsetX: 0,
                    offsetY: 0
                },
                tooltip: {
                    enabled: false
                },
                axisBorder: {
                    show: true,
                    color: this.props.linesColor,
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
                    color: this.props.textColor,
                    fontSize: '14px',
                    fontFamily: "Helvetica"
                }
            },
            grid: {
                borderColor: this.props.linesColor,
            },
        },
    };

    chartContext = null

    constructor(props)
    {
        super(props);
        let self = this;
        this.state.options.chart.events = {
            mounted: function (chartContext, config) {
                self.chartContext = chartContext;
            }
        };
        this.refresh();
    }

    render()
    {
        return (
            <div className="card">
                <div className="card-header">Market Statistics</div>
                <div className="card-body">
                    <div className="chart">
                        <ReactApexChart options={this.state.options} series={this.state.series} type="bar" height={this.props.height} />
                    </div>
                </div>
            </div>
        );
    }

    refresh()
    {
        let self = this;
        RequestHelper.fetch('/api/marketDelta/BTCUSDT/' + this.props.fromTime + '/' + this.props.toTime + '/' + this.props.interval, {}, response => {
            let series = [{data:response.data}];
            self.chartContext.updateSeries(series);
        }, error => console.log(error));
    }

    componentDidMount()
    {
        let self = this;
        this.refresh();
    }

    componentDidUpdate(prevProps, prevState, snapshot) {
    }
};

export default MarketDeltaChart;
