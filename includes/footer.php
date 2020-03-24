</body>
    <script type="text/javascript">

        document.getElementById('filter-submit').onclick = function(e) {
            e.preventDefault();
            var locationId = document.getElementById('countries').value;
            var dateFrom = document.getElementById('date-from').value;
            var dateTo = document.getElementById('date-to').value;
            

            updateTable(dateFrom, dateTo, locationId);
        }

        updateTable('<?= date('Y-m-d', strtotime('-1 day')) ?>', '<?= date('Y-m-d') ?>', 0);

        function updateTable(dateFrom, dateTo, locationId) {
            fetch('renderer.php?from=' + dateFrom + '&to=' + dateTo + '&location_id=' + locationId)
                .then((response) => {
                    return response.json();
                })
                .then((data) => {
                    console.log(data);
                    document.getElementById('infections-table').innerHTML = data.data;
                });
        }

    </script>
</html>
