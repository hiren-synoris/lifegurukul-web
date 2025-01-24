<label class="renewing_subscriptions">Apple In-App Purchase</label><br>
<select class="custom-select form-control renewing_subscriptions w-50"
    id="renewing_subscriptions" name="renewing_subscriptions">
    <option value="">Select Renewing Subscriptions</option>
    @foreach ($renewingSubscriptions as $renewingSubscriptionss)
        <option value="{{ $renewingSubscriptionss->productId }}">
            {{ $renewingSubscriptionss->name }}</option>
    @endforeach
</select>
