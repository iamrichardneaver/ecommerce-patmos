<form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
    @csrf
    <input type="hidden" name="payment_method" value="toyyibpay">
    <div class="form-group row">
        <input type="hidden" name="types[]" value="TOYYIBPAY_KEY">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('TOYYIBPAY KEY') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="TOYYIBPAY_KEY"
                value="{{ env('TOYYIBPAY_KEY') }}" placeholder="{{ translate('TOYYIBPAY KEY') }}"
                required>
        </div>
    </div>
    <div class="form-group row">
        <input type="hidden" name="types[]" value="TOYYIBPAY_CATEGORY">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('TOYYIBPAY CATEGORY') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="TOYYIBPAY_CATEGORY"
                value="{{ env('TOYYIBPAY_CATEGORY') }}"
                placeholder="{{ translate('TOYYIBPAY CATEGORY') }}" required>
        </div>
    </div>
    <div class="form-group row">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('ToyyibPay Sandbox Mode') }}</label>
        </div>
        <div class="col-md-8">
            <label class="aiz-switch aiz-switch-success mb-0">
                <input value="1" name="toyyibpay_sandbox" type="checkbox"
                    @if (get_setting('toyyibpay_sandbox') == 1) checked @endif>
                <span class="slider round"></span>
            </label>
        </div>
    </div>
    <div class="form-group mb-0 text-right">
        <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
    </div>
</form>