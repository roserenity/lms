function exportToCSV(file_name, res){

    console.log(file_name)
    file_name = file_name.replace(/\./g, ' ').replace(/csv$/, '') + '.csv';
    console.log(file_name)

    const reader = new FileReader();
    reader.onload = function(event) {
        const csvText = event.target.result;

        const downloadLink = document.createElement('a');
        downloadLink.href = URL.createObjectURL(new Blob([csvText], { type: 'text/csv' }));
        downloadLink.download = file_name;
        downloadLink.click();
    };

    reader.readAsText(res);
}

function exportToPDF(file_name, res) {
    const reader = new FileReader();
    reader.onload = function(event) {
        const downloadLink = document.createElement('a');
        downloadLink.href = URL.createObjectURL(new Blob([res], { type:  'application/pdf' }));
        downloadLink.download = `${file_name} - Official Receipt.pdf`;
        downloadLink.click();
    };

    reader.readAsText(res);
}

export default {
    install(Vue) {
        Vue.prototype.$exportToCSV = exportToCSV
        Vue.prototype.$exportToPDF = exportToPDF
    }
}