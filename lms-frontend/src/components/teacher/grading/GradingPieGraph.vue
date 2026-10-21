<template>
    <Pie
      :chart-options="chartOptions"
      :chart-data="getChartData"
      :width="width"
      :height="height"
    />
  </template>
  
  <script>
  import { Pie } from 'vue-chartjs/legacy'
  
  import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    ArcElement,
    CategoryScale
  } from 'chart.js'
  
  ChartJS.register(Title, Tooltip, Legend, ArcElement, CategoryScale)
  
  export default {
    name: 'PieChart',
    components: {
      Pie
    },
    props: ['graphLabel', 'graphData'],
    methods: {
      generateColor() {
          const red = Math.floor(Math.random() * 256);
          const green = Math.floor(Math.random() * 256);
          const blue = Math.floor(Math.random() * 256);
          return `rgb(${red}, ${green}, ${blue})`;
      },
    },
    computed: {
      getChartData(){
        let _data = []
        let _colors = []


        this.graphLabel.forEach(label => {
          let _randColor = this.generateColor()

          while(_colors.includes(_randColor)) {
              _randColor = this.generateColor()
          }

          _colors.push(_randColor)
          _data.push(100 / this.graphLabel.length)
        });

        let _chartData = {
          labels: this.graphLabel,
          datasets: [
            {
              backgroundColor: _colors,
              data: this.graphData
            }
          ]
        }

        return _chartData
      }
    },
    data:() => ({
        chartOptions: {
          responsive: true,
          maintainAspectRatio: false
        },
        width: 400,
        height: 400
    })
  }
  </script>
  