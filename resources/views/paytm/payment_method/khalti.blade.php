<form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
    @csrf
    <input type="hidden" name="payment_method" value="khalti">

    <div class="form-group row">
        <input type="hidden" name="types[]" value="KHALTI_SECRET_KEY">
        <div class="col-lg-4">
            <label class="col-from-label">{{ translate('KHALTI  SECRET  KEY') }}</label>
        </div>
        <div class="col-lg-8">
            <input type="text" class="form-control" name="KHALTI_SECRET_KEY"
                value="{{ env('KHALTI_SECRET_KEY') }}"
                placeholder="{{ translate('KHALTI  SECRET  KEY') }}" required>
        </div>
    </div>
    <div class="form-group row">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('Sandbox Mode') }}</label>
        </div>
        <div class="col-md-8">
            <label class="aiz-switch aiz-switch-success mb-0">
                <input value="1" name="khalti_sandbox" type="checkbox"
                    @if (get_setting('khalti_sandbox') == 1) checked @endif>
                <span class="slider round"></span>
            </label>
        </div>
    </div>
    <div class="form-group mb-0 text-right">
        <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
    </div>
</form>