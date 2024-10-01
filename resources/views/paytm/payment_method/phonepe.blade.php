<form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
    @csrf
    <input type="hidden" name="payment_method" value="phonepe">

    <div class="form-group row">
        <input type="hidden" name="types[]" value="PHONEPE_MERCHANT_ID">
        <div class="col-lg-4">
            <label class="col-from-label">{{ translate('Merchant Id') }}</label>
        </div>
        <div class="col-lg-8">
            <input type="text" class="form-control" name="PHONEPE_MERCHANT_ID"
                value="{{ env('PHONEPE_MERCHANT_ID') }}"
                placeholder="{{ translate('PHONEPE MERCHANT ID') }}" required>
        </div>
    </div>

    <div class="form-group row">
        <input type="hidden" name="types[]" value="PHONEPE_SALT_KEY">
        <div class="col-lg-4">
            <label class="col-from-label">{{ translate('PHONEPE_SALT KEY') }}</label>
        </div>
        <div class="col-lg-8">
            <input type="text" class="form-control" name="PHONEPE_SALT_KEY"
                value="{{ env('PHONEPE_SALT_KEY') }}"
                placeholder="{{ translate('PHONEPESALT KEY') }}" required>
        </div>
    </div>

    <div class="form-group row">
        <input type="hidden" name="types[]" value="PHONEPE_SALT_INDEX">
        <div class="col-lg-4">
            <label class="col-from-label">{{ translate('PHONEPE SALT INDEX') }}</label>
        </div>
        <div class="col-lg-8">
            <input type="text" class="form-control" name="PHONEPE_SALT_INDEX"
                value="{{ env('PHONEPE_SALT_INDEX') }}"
                placeholder="{{ translate('PHONEPE SALT INDEX') }}" required>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('Sandbox Mode') }}</label>
        </div>
        <div class="col-md-8">
            <label class="aiz-switch aiz-switch-success mb-0">
                <input value="1" name="phonepe_sandbox" type="checkbox"
                    @if (get_setting('phonepe_sandbox') == 1) checked @endif>
                <span class="slider round"></span>
            </label>
        </div>
    </div>
    <div class="form-group mb-0 text-right">
        <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
    </div>
</form>