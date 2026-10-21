<template>
    <Bar
      :chart-options="chartOptions"
      :chart-data="getChartData"
      :width="width"
      :height="height"
    />
  </template>
  
  <script>
  import { Bar } from 'vue-chartjs/legacy'
  
  import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale
  } from 'chart.js'
  
  ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)
  
  export default {
    name: 'BarChart',
    components: {
      Bar
    },
    props: ['graphLabel', 'graphData'],
    computed: {
        getChartData(){

            let _chartData = {
                labels: this.graphLabel,
                datasets: [
                  {
                    label: 'No. of Respondents',
                    backgroundColor: '#f87979',
                    data: this.graphData
                  }
                ]
            }

            return _chartData
        }
    },
    data() {
      return {
        chartOptions: {
          responsive: true,
          maintainAspectRatio: false
        },
        width: 400,
        height: 400
      }
    }
  }
  </script>
  