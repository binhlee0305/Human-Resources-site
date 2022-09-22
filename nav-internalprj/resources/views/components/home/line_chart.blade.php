<div class="col-md-12">
    <div class="card sale-card" style="min-height: 422px;">
        <div class="card-header">
            <h3>Resource Usage</h3>
        </div>
        <form action="" method="POST" class="form-search">
            @csrf
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <br>
            <div class="container">
                <div class="row">
                    <div class="container-fluid">
                        <div class="form-group row">
                            <label for="date" class="col-sm-2 col-form-lable">Start Date</label>
                            <div class="col-sm-3">
                                <input type="date" class="form-control input-sm" name="start_date" id="start_date" require />
                            </div>
                            <label for="date" class="col-sm-2 col-form-lable">End Date</label>
                            <div class="col-sm-3">
                                <input type="date" class="form-control input-sm" name="end_date" id="end_date" require />
                            </div>
                            <div class="col-sm-2">
                                <button type="submit" class="btn" name="searchLineChart" title="searchLineChart"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="card-block">
            <div id="deal-analytic-chart" class="chart-shadow" style="height:300px"></div>
        </div>
    </div>
</div>