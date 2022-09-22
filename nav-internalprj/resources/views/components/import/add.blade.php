<div class="ml-2 col-sm-6">
    <form method="post" id="" enctype="multipart/form-data">
    @csrf
        <h3>Import</h3>
        <input type="file" name="file" class="file" id="uploadFile" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" />
        <div class="input-group my-3">
            <input type="text" class="form-control" placeholder="Upload Excel File" id="file" />
            <div class="input-group-append">
                <button id="button" type="button" class="browse btn btn-primary"><i class="ik ik-upload"></i><span>@lang('language.Imports')</span></button>
            </div>
        </div>
    </form>
</div>

<!-- Modal Data-->
<div class="modal fade bd-example-modal-lg" id="message-dialog-data" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
        <h5 class="modal-title-data font-weight-bold"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        </div>
        <div class="modal-body">
            <table id="table-data" class="table">
                  <thead>
                      <tr class="header-table font-weight-bold">
                      </tr>
                  </thead>
                  <tbody class="body-data">
                  </tbody>
            </table>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="close-modal" data-dismiss="modal">@lang('language.Close')</button>
        </div>
    </div>
    </div>
</div>