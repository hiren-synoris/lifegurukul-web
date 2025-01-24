@if(isset($row) && !empty($row))    
    <a class="mx-1" href="javascript:void(0)" data-toggle="modal" title="Reply" data-target="#contactReply{{ $row->id }}">
        <i class="far fa-comment-alt text-success"></i>
    </a>
    <form action="{{ route('contact.reply') }}" method="POST">
        @csrf
        <div class="modal fade" id="contactReply{{ $row->id }}" tabindex="-1" role="dialog" aria-labelledby="contactReplyTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="contactReplyLongTitle">Reply to <strong>{{ $row->name }}</strong></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-break text-justify">
                        <input type="hidden" name="contact_id" value="{{ $row->id }}">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="text" style="background-color:#e9ecef;" class="form-control" readonly name="email" id="email" value="{{ $row->email }}">
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Send Mail</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endif