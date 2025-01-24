<div class="d-md-flex justify-content-between mb-3">
    <h3>Pricing Plans</h3>
    <button class="btn bg-success addPrice" type="button" data-toggle="modal" data-target="#addPlanModal"><i class="fas fa-plus mr-1"></i> Add Plan</button>
</div>
<table id="tbl_course_plans" class="table table-bordered table-hove w-100">
    <thead class="thead-light">
        <tr class="text-center">
            <th></th>
            <th scope="col">Plan Id</th>
            <th scope="col">Plan Name</th>
            <th scope="col">Plan Days</th>
            <th scope="col">Plan Type</th>
            <th scope="col">List Price</th>
            <th scope="col">Final Price</th>
            <th scope="col">Status</th>
            <th scope="col"> Checkout URL (with login)</th>
            <th scope="col">Checkout URL (without login)</th>
            <th scope="col">Web (Default)</th>
            <th scope="col">Iphone (Default)</th>
            <th scope="col">Android (Default)</th>
            <th scope="col">Action</th>
        </tr>
    </thead>
    <tbody id="tbl_course_plan_body"></tbody>
</table>

<!-- Pricing Tab Modal -->
    <!-- Add Plan Modal code start -->
        @include('admin.courses.info.add-course-price')
    <!-- Add Plan Modal code end -->

    <!-- Edit Plan Modal code start (see: edit-course.blade.php) for edit popup modal-->
    <div class="modal fade" id="editFreePlanModal" tabindex="-1" aria-labelledby="editFreePlanModalLabel" aria-hidden="true">
    </div>
    <!-- edit Plan Modal code end -->
