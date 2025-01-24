@if (isset($row->id) && !empty($row->id))
    <a type="button" class="mx-1" data-toggle="modal" data-target="#chatModal{{ $row->id }}">
        <i class="far fa-comment-alt text-success"> </i>
    </a>
    <div class="modal fade" id="chatModal{{ $row->id }}" tabindex="-1" role="dialog"
        aria-labelledby="chatModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="chatModalLabel">Message</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-break text-justify">
                    {{-- Message Listing --}}
                    @if(isset($row->supportChats) && !empty($row->supportChats) && count($row->supportChats) > 0)
                        <div class="direct-chat-messages border border-gray">
                            @foreach ($row->supportChats as $supportChat)
                                @if($supportChat->created_by != auth()->id())
                                <div class="direct-chat-msg">
                                    <div class="direct-chat-infos clearfix">
                                        <span class="direct-chat-name float-left">{{ !empty($supportChat->user->name) ? ucfirst($supportChat->user->name) : '' }}</span>
                                        <span class="direct-chat-timestamp float-right">{{ !empty($supportChat->created_at) ? date('d M y H:i A', strtotime($supportChat->created_at)) : '' }}</span>
                                    </div>
                                    <img class="direct-chat-img" src="{{ asset('admin/dist/img/avatar5.png') ?? '' }}" alt="message user image">
                                    <div class="direct-chat-text">
                                        {{ !empty($supportChat->message) ? $supportChat->message : '' }}
                                    </div>
                                </div>
                                @endif
                                @if($supportChat->created_by == auth()->id())
                                    <div class="direct-chat-msg right">
                                        <div class="direct-chat-infos clearfix">
                                            <span class="direct-chat-name float-right">{{ !empty($supportChat->user->name) ? ucfirst($supportChat->user->name) : '' }}</span>
                                            <span class="direct-chat-timestamp float-left">{{ !empty($supportChat->created_at) ? date('d M y H:i A', strtotime($supportChat->created_at)) : '' }}</span>
                                        </div>
                                        <img class="direct-chat-img" src="{{ asset('admin/dist/img/avatar.png') ?? '' }}" alt="Admin user image">
                                        <div class="direct-chat-text">
                                            {{ !empty($supportChat->message) ? $supportChat->message : '' }}
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    @php
                        $targetEmail = '';
                        if (Auth::user()->hasRole('admin')) {
                            $targetEmail = @$row->user->email   ;
                        }else {
                            $targetEmail = arrayToString(App\Models\User::role('admin')->pluck('email')->toArray());
                        }
                    @endphp

                    <form method="POST" action="{{ route('support.reply') }}">
                        @csrf
                        <input type="hidden" name="support_id" id="support_id" value="{{ $row->id ?? NULL }}">

                        @if(isset($targetEmail) && !empty($targetEmail))
                            <input type="hidden" name="target_email" id="target_email" value="{{ $targetEmail }}">
                        @endif

                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea class="form-control" id="message" name="message" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
