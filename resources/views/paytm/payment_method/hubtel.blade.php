<form class="form-horizontal" action="{{ route('hubtel.update_credentials') }}" method="POST">
    @csrf
    <div class="form-group row">
        <input type="hidden" name="types[]" value="HUBTEL_API_KEY">
        <div class="col-lg-4">
            <label class="col-from-label">{{ translate('HUBTEL API KEY') }}</label>
        </div>
        <div class="col-lg-6">
            <input type="text" class="form-control" name="HUBTEL_API_KEY"
                value="{{ env('HUBTEL_API_KEY') }}" placeholder="HUBTEL API KEY" required>
        </div>
    </div>
    <div class="form-group row">
        <input type="hidden" name="types[]" value="HUBTEL_MERCHANT_ACCOUNT_NUMBER">
        <div class="col-lg-4">
            <label class="col-from-label">{{ translate('HUBTEL MERCHANT ACCOUNT NUMBER') }}</label>
        </div>
        <div class="col-lg-6">
            <input type="text" class="form-control" name="HUBTEL_MERCHANT_ACCOUNT_NUMBER"
                value="{{ env('HUBTEL_MERCHANT_ACCOUNT_NUMBER') }}" placeholder="HUBTEL MERCHANT ACCOUNT NUMBER" required>
        </div>
    </div>
    <div class="form-group mb-0 text-right">
        <button type="submit" class="btn btn-primary">{{ translate('Save') }}</button>
    </div>
</form>
