<template>
    <section >
      <apexchart class="fade" id="horizontalGraph" :height="500" type="bar" :options="{...chartOptions, colors: colors}" :series="series"></apexchart>
    </section>
</template>

<style>
    #horizontalGraph .apexcharts-tooltip-title {
        color: #ffffff !important;
        background-color: rgba(30,30,30,.8) !important;
        font-size: 13px !important;
    }
    #horizontalGraph .apexcharts-tooltip-body {
        font-size: 11px !important;
        padding-left: 5px !important;
        padding-right: 5px !important;
        padding-bottom: 8px !important;
    }
</style>

<script>

export default {
    props: {
        series: {
            type: Array,
            default: [{
                data: [{
                    x: '',
                    y: 0
                }]
            }]
        },
        colors: {
            type: Array,
            default: () => (['#B52210'])
        },
        filename: {
            type: String,
            default: ''
        },
    },
    data: () => ({
        chartOptions: {
            chart: {
                height: '100%',
                width: '100%',
            }
        }
    }),
    mounted() {
        this.chartOptions = {
            plotOptions: {
                bar: {
                    borderRadius: 8,
                    borderRadiusApplication: 'end',
                    //horizontal: true,
                    dataLabels: {
                        position: 'top'
                    }
                },
            },
            dataLabels: {
                enabled: true,
                style: {
                    colors: ['#333']
                },
                textAnchor: 'middle',
                offsetX: 0,
                offsetY: -30    
            },
            chart: {
                height: '100%',
                width: '100%',
                
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                    animateGradually: {
                        enabled: true,
                        delay: 150
                    },
                    dynamicAnimation: {
                        enabled: true,
                        speed: 350
                    }
                },
                zoom: {
                    enabled: true,
                    type: 'x',  
                    autoScaleYaxis: false,  
                    zoomedArea: {
                        fill: {
                            color: '#90CAF9',
                            opacity: 0.4
                        },
                        stroke: {
                            color: '#0D47A1',
                            opacity: 0.4,
                            width: 1
                        }
                    }
                },
                toolbar: {
                    show: true,
                    tools: {
                        download: true,
                        selection: true,
                        zoom: true,
                        zoomin: true,
                        zoomout: true,
                        pan: true,
                        reset: true | '<img src="/static/icons/reset.png" width="20">',
                    },
                    export: {
                        csv: {
                            filename: this.filename,
                            headerCategory: '',
                        },
                        svg: {
                            filename: this.filename,
                        },
                        png: {
                            filename: this.filename,
                        }
                    },
                    autoSelected: 'zoom' 
                },
                redrawOnWindowResize: true,
                redrawOnParentResize: true
            },
            noData: {
                text: "There's no data",
                align: 'center',
                verticalAlign: 'middle',
                offsetX: 0,
                offsetY: 0
            },
            tooltip: {
                enabled: true,
                shared: true,
                followCursor: true,
                intersect: false,
                custom: function({series, seriesIndex, dataPointIndex, w}) {
                    var data = w.globals.initialSeries[seriesIndex].data[dataPointIndex];
                    
                    var _keys = Object.keys(data)

                    const tooltipContent = _keys.map((element) => {
                        if (element !== 'x' && element !== 'y') {
                            return `<div><b class="secondary-1--text">${element.toLocaleUpperCase().replaceAll('_', ' ')}</b> : ${data[element]}</div>`;
                        }
                        return '';
                    }).join('');

                    return '<div class="apexcharts-tooltip-title"><b>' + data.x.join(' ') + '</b></div>'
                        + '<div class="apexcharts-tooltip-body">' + tooltipContent + '</div>';
                }
            },
            xaxis: {
                tickPlacement: 'on',
                tickAmount: 10,
                labels: {
                    rotate: 0,
                    trim: false,
                    minHeight: '100',
                    style: {
                        fontSize: 10,
                    }
                },
            },
            responsive: [
                {
                    breakpoint: 1000,
                    options: {
                        chart: {
                            height: 500
                        },
                        plotOptions: {
                            bar: {
                                horizontal: false
                            }
                        },
                        legend: {
                            position: "bottom",
                        },
                        xaxis: {
                            tickPlacement: 'on',
                            tickAmount: 2,
                            labels: {
                                rotate: 280,
                                trim: true
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            style: {
                                colors: ['#333']
                            },
                            offsetX: 0,
                            offsetY: -20     

                        },
                    },
                }
            ],
        }
    },
    computed: {
        computeHeight(){
            return this.series[0].data.length < 10 ?  this.series[0].data.length * 100 : ''
        },
    },
};
</script>