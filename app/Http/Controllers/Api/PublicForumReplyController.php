<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helper\Helper;
use App\Models\Course;
use App\Models\User;
use App\Models\Learner;
use Illuminate\Support\Facades\Mail;
use App\Mail\DiscussionChatMail;
use App\Models\PublicForum;
use Illuminate\Support\Carbon;
use App\Models\PublicForumReply;
use Illuminate\Support\Facades\Validator;

class PublicForumReplyController extends Controller
{
    public function getDiscussionQuestion(Request $request)
    {



        $learner_id = $request->user_id;

        $validator = Validator::make($request->all(), [
            'page' => 'nullable',
            'course_id' => 'required',
            'per_page' => 'nullable',
        ]);

        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }

        $page_num = isset($request->page) && !empty($request->page) ? $request->page : 1;
        $per_page = isset($request->per_page) && !empty($request->per_page) ? $request->per_page : 5;

        $getPublicForums = PublicForum::select('public_forums.id', 'public_forums.created_by_admin', 'public_forums.created_by_learner', 'public_forums.course_id', 'public_forums.description', 'public_forums.created_at', 'public_forums.image')->where('course_id', $request->course_id)->orderBy("public_forums.id", "desc")->get();

        $getPublicForums = cpaginate($getPublicForums, $per_page, $page_num);

        if ($getPublicForums->count() > 0) {
            foreach ($getPublicForums as $value) {

                // if (isset($value->description) && !empty($value->description)) {
                //     $value->description = strip_tags($value->description);
                // }

                if (isset($value->description) && !empty($value->description)) {
                    // $value->description = strip_tags($value->description);
                    // $value->description = str_replace('&nbsp;', ' ', $value->description);
                    $value->description = html_entity_decode($value->description);
                    // $value->description = str_replace(' ', '', $value->description);
                }
                if (isset($value->image) && !empty($value->image)) {
                    $value->image = !empty($value->image) ? Helper::getImageUrl($value->image) : '';
                }

                if (isset($value->created_by_admin) && !empty($value->created_by_admin)) {
                    $value->created_by = User::select('id', 'name', 'profile_picture')->where('id', $value->created_by_admin)->first();

                    if ($value->created_by) {
                        $value->created_by->profile_picture = !empty($value->created_by->profile_picture) ? Helper::getImageUrl($value->created_by->profile_picture) : '';
                    } else {
                        $value->created_by = new \stdClass();
                        $value->created_by->id = "";
                        $value->created_by->name = "";
                        $value->created_by->profile_picture = "";
                    }

                    if ($value->created_by_admin != "") {
                        $value->created_admin = true;
                    } else {
                        $value->created_admin = false;
                    }

                    if ($value->created_by_learner != "") {
                        $value->created_user = true;
                    } else {
                        $value->created_user = false;
                    }

                    if ($value->created_by_admin == $learner_id) {
                        $value->is_mine = true;
                    } else {
                        $value->is_mine = false;
                    }
                    // $value->created_user = false;
                } else if (isset($value->created_by_learner) && !empty($value->created_by_learner)) {
                    $value->created_by = @Learner::select('id', 'name', 'profile_pic as profile_picture')->where('id', $value->created_by_learner)->first();

                    if ($value->created_by) {
                        $value->created_by->profile_picture = !empty($value->created_by->profile_picture) ? Helper::getImageUrl($value->created_by->profile_picture) : '';
                    } else {
                        $value->created_by = new \stdClass();
                        $value->created_by->id = "";
                        $value->created_by->name = "";
                        $value->created_by->profile_picture = "";
                    }

                    if ($value->created_by_learner == $learner_id) {
                        $value->is_mine = true;
                    } else {
                        $value->is_mine = false;
                    }

                    if ($value->created_by_admin != "") {
                        $value->created_admin = true;
                    } else {
                        $value->created_admin = false;
                    }

                    if ($value->created_by_learner != "") {
                        $value->created_user = true;
                    } else {
                        $value->created_user = false;
                    }
                }


                // dump($value->created_by->profile_picture);
                unset($value->created_by_learner, $value->created_by_admin);
            }

            $is_paginated = TRUE;


            $data = Helper::apiResonse(1, "Success", $getPublicForums, $is_paginated);
            return response()->json($data, 200);
        } else {
            $data = Helper::apiResonse(0, "No Records found", []);
            return response()->json($data, 200);
        }
    }

    public function getDiscussionReply(Request $request)
    {


        $learner_id = request()->user_id;

        $validator = Validator::make($request->all(), [
            'public_forums_id' => 'required|exists:public_forums,id'
        ]);

        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }

        $page_num = isset($request->page) && !empty($request->page) ? $request->page : 1;
        $per_page = isset($request->per_page) && !empty($request->per_page) ? $request->per_page : 5;

        $getPublicForumsReply = PublicForumReply::select('public_forums_reply.id', 'public_forums_reply.public_forums_id', 'public_forums_reply.reply', 'public_forums_reply.reply_by_admin', 'public_forums_reply.reply_by_learner', 'public_forums_reply.created_at', 'public_forums_reply.image')->where('public_forums_id', $request->public_forums_id)->orderBy("public_forums_reply.id", "desc")
            ->get();

        $getPublicForumsReply = cpaginate($getPublicForumsReply, $per_page, $page_num);

        if ($getPublicForumsReply->count() > 0) {
            foreach ($getPublicForumsReply as $value) {




                if (isset($value->image) && !empty($value->image)) {
                    $value->image = !empty($value->image) ? Helper::getImageUrl($value->image) : '';
                }

                $value->reply = html_entity_decode($value->reply);

                if (isset($value->reply_by_admin) && !empty($value->reply_by_admin)) {


                    if ($value->reply_by_admin == $learner_id) {
                        $value->is_mine = true;
                    } else {
                        $value->is_mine = false;
                    }

                    if ($value->reply_by_admin != "") {
                        $value->created_admin = true;
                    } else {
                        $value->created_admin = false;
                    }

                    if ($value->reply_by_learner != "") {
                        $value->created_user = true;
                    } else {
                        $value->created_user = false;
                    }

                    $value->reply_by = User::select('id', 'name', 'profile_picture')->where('id', $value->reply_by_admin)->first();
                    if ($value->reply_by) {
                        $value->reply_by->profile_picture = !empty($value->reply_by->profile_picture) ? Helper::getImageUrl($value->reply_by->profile_picture) : '';
                    } else {
                        $value->reply_by = new \stdClass();
                        $value->reply_by->id = "";
                        $value->reply_by->name = "";
                        $value->reply_by->profile_picture = "";
                    }
                } else if (isset($value->reply_by_learner) && !empty($value->reply_by_learner)) {

                    if ($value->reply_by_learner == $learner_id) {
                        $value->is_mine = true;
                    } else {
                        $value->is_mine = false;
                    }

                    if ($value->reply_by_admin != "") {
                        $value->created_admin = true;
                    } else {
                        $value->created_admin = false;
                    }

                    if ($value->reply_by_learner != "") {
                        $value->created_user = true;
                    } else {
                        $value->created_user = false;
                    }

                    $value->reply_by = Learner::select('id', 'name', 'profile_pic as profile_picture')->where('id', $value->reply_by_learner)->first();

                    if ($value->reply_by) {
                        $value->reply_by->profile_picture = !empty($value->reply_by->profile_picture) ? Helper::getImageUrl($value->reply_by->profile_picture) : '';
                    } else {
                        $value->reply_by = new \stdClass();
                        $value->reply_by->id = "";
                        $value->reply_by->name = "";
                        $value->reply_by->profile_picture = "";
                    }
                }
            }


            unset($value->created_by_learner, $value->created_by_admin);

            $is_paginated = TRUE;

            $data = Helper::apiResonse(1, "Success", $getPublicForumsReply, $is_paginated);
            return response()->json($data, 200);
        } else {
            $data = Helper::apiResonse(0, "No Records found", []);
            return response()->json($data, 200);
        }
    }

    public function addDiscussionQuestionReply(Request $request)
    {

        $learner_id = request()->user_id;

        $validator = Validator::make($request->all(), [
            'public_forums_id' => 'nullable'
        ]);

        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }

        if ($request->public_forums_id) {

            $data = [
                'public_forums_id' => $request->public_forums_id,
                'reply' => $request->reply,
                'reply_by_learner' => $learner_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()

            ];

            if ($request->hasFile('image')) {

                $file = $request->file('image');

                $filename = time() . '_' . $file->getClientOriginalName();

                $location = $file->storeAs('discussion/Image', $filename);

                $data['image'] = $location;
            }


            PublicForumReply::create($data);


            $getForum = PublicForum::where('id', $request['public_forums_id'])->first();
            $course_id = $getForum->course_id;
            $course = Course::where('id', $course_id)->first();

            $user = User::where('id', $course->instructor_id)->select(['id', 'name', 'email'])->first();

            $sender = Learner::select(['id', 'name', 'email'])->where('id', auth()->id())->first();


            $url = route('courses.public-forum', ['id' => $course_id]);
            $subject = "You got reply in " . $course->title . " by " . $sender->name;

            if (isset($user) && !empty($user)) {
                Mail::to($user->email)->send(new DiscussionChatMail($subject, $user, $sender, $course_id, $url));
            }

            $page_num = isset($request->page) && !empty($request->page) ? $request->page : 1;
            $per_page = isset($request->per_page) && !empty($request->per_page) ? $request->per_page : 50;

            $getPublicForumsReply = PublicForumReply::select('public_forums_reply.id', 'public_forums_reply.public_forums_id', 'public_forums_reply.reply', 'public_forums_reply.reply_by_admin', 'public_forums_reply.reply_by_learner', 'public_forums_reply.created_at', 'public_forums_reply.image')->where('public_forums_id', $request->public_forums_id)->orderBy("public_forums_reply.id", "desc")
                ->get();

            $getPublicForumsReply = cpaginate($getPublicForumsReply, $per_page, $page_num);

            if ($getPublicForumsReply->count() > 0) {
                foreach ($getPublicForumsReply as $value) {

                    if (isset($value->image) && !empty($value->image)) {
                        $value->image = !empty($value->image) ? Helper::getImageUrl($value->image) : '';
                    }

                    $value->reply = strip_tags($value->reply);

                    if (isset($value->reply_by_admin) && !empty($value->reply_by_admin)) {
                        $value->reply_by = User::select('id', 'name', 'profile_picture')->where('id', $value->reply_by_admin)->first();

                        if ($value->reply_by_admin == $learner_id) {
                            $value->is_mine = true;
                        } else {
                            $value->is_mine = false;
                        }

                        if ($value->reply_by_admin != "") {
                            $value->created_admin = true;
                        } else {
                            $value->created_admin = false;
                        }

                        if ($value->reply_by_learner != "") {
                            $value->created_user = true;
                        } else {
                            $value->created_user = false;
                        }

                        if ($value->reply_by) {
                            $value->reply_by->profile_picture = !empty($value->reply_by->profile_picture) ? Helper::getImageUrl($value->reply_by->profile_picture) : '';
                        } else {
                            $value->reply_by = new \stdClass();
                            $value->reply_by->id = "";
                            $value->reply_by->name = "";
                            $value->reply_by->profile_picture = "";
                        }
                    } else if (isset($value->reply_by_learner) && !empty($value->reply_by_learner)) {
                        $value->reply_by = Learner::select('id', 'name', 'profile_pic as profile_picture')->where('id', $value->reply_by_learner)->first();

                        if ($value->reply_by_learner == $learner_id) {
                            $value->is_mine = true;
                        } else {
                            $value->is_mine = false;
                        }

                        if ($value->reply_by_admin != "") {
                            $value->created_admin = true;
                        } else {
                            $value->created_admin = false;
                        }

                        if ($value->reply_by_learner != "") {
                            $value->created_user = true;
                        } else {
                            $value->created_user = false;
                        }


                        if ($value->reply_by) {
                            $value->reply_by->profile_picture = !empty($value->reply_by->profile_picture) ? Helper::getImageUrl($value->reply_by->profile_picture) : '';
                        } else {
                            $value->reply_by = new \stdClass();
                            $value->reply_by->id = "";
                            $value->reply_by->name = "";
                            $value->reply_by->profile_picture = "";
                        }
                    }
                }

                unset($value->created_by_learner, $value->created_by_admin);

                $is_paginated = TRUE;

                $data = Helper::apiResonse(1, "Success", $getPublicForumsReply, $is_paginated);
                return response()->json($data, 200);
            } else {
                $data = Helper::apiResonse(0, "No Records found", []);
                return response()->json($data, 400);
            }
        } else {

            $data = [
                'description' => $request->description,
                'created_by_learner' => $learner_id,
                'course_id' => $request->course_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()

            ];

            if ($request->hasFile('image')) {

                $file = $request->file('image');

                $filename = time() . '_' . $file->getClientOriginalName();

                $location = $file->storeAs('discussion/Image', $filename);

                $data['image'] = $location;
            }

            PublicForum::create($data);


            $course_id = $request['course_id'];
            $course = Course::where('id', $request['course_id'])->first();

            $user = User::where('id', $course->instructor_id)->select(['id', 'name', 'email'])->first();

            $sender = Learner::select(['id', 'name', 'email'])->where('id', auth()->id())->first();

            $url = route('courses.public-forum', ['id' => $course_id]);
            $subject = "New discussion comment added in " . $course->title . " by " . $sender->name;

            if (isset($user) && !empty($user)) {
                Mail::to($user->email)->send(new DiscussionChatMail($subject, $user, $sender, $course, $url));
            }

            $page_num = isset($request->page) && !empty($request->page) ? $request->page : 1;
            $per_page = isset($request->per_page) && !empty($request->per_page) ? $request->per_page : 5;

            $getPublicForums = PublicForum::select('public_forums.id', 'public_forums.created_by_admin', 'public_forums.created_by_learner', 'public_forums.course_id', 'public_forums.description', 'public_forums.created_at', 'public_forums.image')->where('course_id', $request->course_id)->orderBy("public_forums.id", "desc")
                ->get();

            $getPublicForums = cpaginate($getPublicForums, $per_page, $page_num);

            if ($getPublicForums->count() > 0) {
                foreach ($getPublicForums as $value) {

                    if (isset($value->image) && !empty($value->image)) {
                        $value->image = !empty($value->image) ? Helper::getImageUrl($value->image) : '';
                    }

                    if (isset($value->created_by_admin) && !empty($value->created_by_admin)) {
                        $value->created_by = User::select('id', 'name', 'profile_picture')->where('id', $value->created_by_admin)->first();

                        if ($value->created_by_admin == $learner_id) {
                            $value->is_mine = true;
                        } else {
                            $value->is_mine = false;
                        }

                        if ($value->created_by_admin != "") {
                            $value->created_admin = true;
                        } else {
                            $value->created_admin = false;
                        }

                        if ($value->created_by_learner != "") {
                            $value->created_user = true;
                        } else {
                            $value->created_user = false;
                        }

                        if ($value->created_by) {
                            $value->created_by->profile_picture = !empty($value->created_by->profile_picture) ? Helper::getImageUrl($value->created_by->profile_picture) : '';
                        } else {
                            $value->created_by = new \stdClass();
                            $value->created_by->id = "";
                            $value->created_by->name = "";
                            $value->created_by->profile_picture = "";
                        }
                    } else if (isset($value->created_by_learner) && !empty($value->created_by_learner)) {
                        $value->created_by = Learner::select('id', 'name', 'profile_pic as profile_picture')->where('id', $value->created_by_learner)->first();

                        if ($value->created_by_learner == $learner_id) {
                            $value->is_mine = true;
                        } else {
                            $value->is_mine = false;
                        }

                        if ($value->created_by_admin != "") {
                            $value->created_admin = true;
                        } else {
                            $value->created_admin = false;
                        }

                        if ($value->created_by_learner != "") {
                            $value->created_user = true;
                        } else {
                            $value->created_user = false;
                        }

                        if ($value->created_by) {
                            $value->created_by->profile_picture = !empty($value->created_by->profile_picture) ? Helper::getImageUrl($value->created_by->profile_picture) : '';
                        } else {
                            $value->created_by = new \stdClass();
                            $value->created_by->id = "";
                            $value->created_by->name = "";
                            $value->created_by->profile_picture = "";
                        }
                    }

                    unset($value->created_by_learner, $value->created_by_admin);
                }

                $is_paginated = TRUE;

                $data = Helper::apiResonse(1, "Success", $getPublicForums, $is_paginated);
                return response()->json($data, 200);
            } else {
                $data = Helper::apiResonse(0, "No Records found", []);
                return response()->json($data, 400);
            }
        }
    }


    public function deleteDiscussion(Request $request)
    {
        if ($request->isDeleteComment == "1") {

            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:public_forums,id'
            ]);

            if ($validator->fails()) {
                $data = Helper::apiResonse(0, $validator->messages(), []);
                return response()->json($data, 400);
            }

            PublicForum::where('id', $request->id)->delete();
            PublicForumReply::where('public_forums_id', $request->id)->delete();

            $data = Helper::apiResonse(1, "Comment deleted successfully", []);
            return response()->json($data, 200);
        } else {

            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:public_forums_reply,id'
            ]);

            if ($validator->fails()) {
                $data = Helper::apiResonse(0, $validator->messages(), []);
                return response()->json($data, 400);
            }

            PublicForumReply::where('id', $request->id)->delete();

            $data = Helper::apiResonse(1, "Reply deleted successfully", []);
            return response()->json($data, 200);
        }
    }
}
