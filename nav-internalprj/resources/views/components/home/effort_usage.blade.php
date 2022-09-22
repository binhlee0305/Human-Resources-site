<div class="col-md-12">
    <div class="card">
        <div class="card-header d-block"><h3>Resource Usage</h3></div>
        <form action="" method="post" class="search-effortusage">
            @csrf
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <br>
            <div class="container">
                <div class="row">
                    <div class="container-fluid">
                        <div class="form-group row">
                            <label for="date" class="col-sm-2 col-form-label">Start Date</label>
                            <div class="col-sm-3">
                                <input type="date" name="fromDate" id="fromDate" class="form-control input-sm" required/>
                            </div>
                            <label for="date" class="col-sm-2 col-form-label">End Date</label>
                            <div class="col-sm-3">
                                <input type="date" name="toDate" id="toDate" class="form-control input-sm" required/>
                            </div>
                            <div class="col-sm-2">
                                <button type="submit" class="btn" name="search" title="Search"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        
        <div id="loading">
            <img src="{{asset('assets/img/loading.gif')}}" alt="Loading..."/>
        </div>

        <div class="card-body" id="spreadsheetdiv">
            <div id="spreadsheet" class=""></div>
        </div>
    </div>
</div>

