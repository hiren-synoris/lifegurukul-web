<label for="content">Campaigns</label><br>
<select class="form-control get_template" name="campaigns" id="campaigns"">
    <option selected value="">Select Campaigns</option>
    @foreach($data as $val)
    <option value="{{ $val->campaign_id }}">{{ $val->name }}</option>
    @endforeach
</select>
