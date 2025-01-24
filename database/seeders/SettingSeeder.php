<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Settings;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->truncate();
        $modules = ['settings'];
        foreach ($modules as $key => $value) {
            $Settings =
            [
                'address' => ['Address', 'textarea', '3556 Beech Street, San Francisco,
                California, CA 94108', true],
                'contact_no' => ['Contact Number', 'text', '+91 72111 28282', true],
                'message' => ['Message', 'text', 'lifegurukul@example.com', false],
                'logo' => ['Logo', 'file', NULL, false],
                'favicon' => ['Front Favicon', 'file', NULL, true],
                'top_mobile_number' => ['Top Mobile Number', 'text', '+91 72111 28282', true],
                'contact_us' => ['Contact Us', 'text', NULL, true],
                'opening_hours_weekdays' => ['Opening Hours (Monday To Friday)', 'time', '10:00', true],
                'closing_hours_weekdays' => ['Closing Hours (Monday To Friday)', 'time', '16:00', true],
                'opening_hours_sat' => ['Opening Hours (Saturday)', 'time', '10:00', true],
                'closing_hours_sat' => ['Closing Hours (Saturday)', 'time', '16:00', true],
                'footer_about_content' => ['About Content', 'textarea', 'Life Gurukul is an application that includes lessons on career, business, self-improvement, yoga & fitness, relationship management, and much more.', true],
                'footer_addr_company_name' => ['Footer Company Name', 'text', 'Sneh Academic Services Private Limited', true],
                'footer_addr_company_addr' => ['Footer Company Address', 'text', 'A-1 Sentossa Greenland Bungalows
                Near Science City Cross Road, S.P. Ring Road, Bhadaj, Ahmedabad - 380060, Gujarat, India.', true],
                'footer_contact_email' => ['Footer Contact Email', 'text', 'info@lifegurukul.app', true],
                'footer_contact_insta_id' => ['Footer Contact Instagram Id', 'text', '@snehdesai_Lifecoach', true],
                'footer_contact_facebook_id' => ['Footer Contact Facebook Id', 'text', '@snehworld', true],
                'footer_insta_followers' => ['Footer Instagram Followers', 'text', '137', true],
                'footer_facebook_followers' => ['Footer Facebook Followers', 'text', '143', true],
                'footer_youtube_followers' => ['Footer YouTube Followers', 'text', '39', true],
                'footer_linkedin_followers' => ['Footer LinkedIn Followers', 'text', '2', true],
                'footer_insta_icon' => ['Footer Instagram Icon', 'file', 'front/img/insta.svg', true],
                'footer_facebook_icon' => ['Footer Facebook Icon', 'file', 'front/img/fb.svg', true],
                'footer_youtube_icon' => ['Footer YouTube Icon', 'file', 'front/img/yt.svg', true],
                'footer_linkedin_icon' => ['Footer LinkedIn Icon', 'file','front/img/in.svg', true],
                'footer_twitter_icon' => ['Footer Twitter Icon', 'file', 'front/img/twitter.svg', true],
                'backend_favicon' => ['Backend Favicon', 'file', NULL, true],
                'maintenance_mode' => ['Maintenance Mode', 'boolean', 'Put the application into maintenance mode', false, 0],
                'facebook_status' => ['Facebook Status', 'boolean', 'Enable Facebook Login', false, 1],
                'facebook_app_id' => ['Facebook App ID', 'text', NULL, true],
                'facebook_app_secret' => ['Facebook App Secret', 'text', NULL, true] ,
                'facebook_url' => ['Facebook Url', 'text', 'https://m.facebook.com/LifegurukulOfficial/', true] ,
                'google_status' => ['Google Status', 'boolean', 'Enable Google Login', false, 0],
                'google_client_id' => ['Google Client ID', 'text', NULL, true],
                'google_client_secret' => ['Google Client Secret', 'text', NULL, true] ,
                'twitter_status' => ['Twitter Status', 'boolean', 'Enable Twitter Login', false, 0],
                'twitter_client_id' => ['Twitter Client ID', 'text', NULL, true],
                'twitter_client_secret' => ['Twitter Client Secret', 'text', NULL, true] ,
                'twitter_url' => ['Twitter Url', 'text', '', true] ,
                'instagram_status' => ['Instagram Status', 'boolean', 'Enable Instagram Login', false, 0],
                'instagram_client_id' => ['Instagram Client ID', 'text', NULL, true],
                'instagram_client_secret' => ['Instagram Client Secret', 'text', NULL, true] ,
                'instagram_url' => ['Instagram Url', 'text', 'https://www.instagram.com/lifegurukulofficial/', true] ,
                'linkedin_status' => ['LinkedIn', 'boolean', 'Enable LinkedIn Login', false, 0],
                'linkedin_client_id' => ['LinkedIn Client ID', 'text', NULL, true],
                'linkedin_client_secret' => ['LinkedIn Client Secret', 'text', NULL, true] ,
                'linkedin_url' => ['LinkedIn Url', 'text', '', true] ,
                'youtube_status' => ['Youtube', 'boolean', 'Enable Youtube Login', false, 0],
                'youtube_client_id' => ['Youtube Client ID', 'text', NULL, true],
                'youtube_client_secret' => ['Youtube Client Secret', 'text', NULL, true] ,
                'youtube_url' => ['Youtube Url', 'text', 'https://www.youtube.com/channel/UCLQJ1-iNO2Zhao5_rj9IkxA', true] ,
                'razorpay_status' => ['Razorpay Status', 'boolean', 'Enable Razorpay', false, 0],
                'razorpay_sandbox' => ['Razorpay Sandbox', 'boolean', 'Razorpay Sandbox', false, 1],
                'razorpay_key' => ['Razorpay Key', 'text', NULL, true],
                'razorpay_secret' => ['Razorpay Secret', 'text', NULL, true],
                'razorpay_sandbox_key' => ['Razorpay Sandbox Key', 'text', NULL, true],
                'razorpay_sandbox_secret' => ['Razorpay Sandbox Secret', 'text', NULL, true],
                'instamojo_status' => ['Instamojo Status', 'boolean', 'Enable Instamojo', false, 0],
                'instamojo_sandbox' => ['Instamojo Sandbox', 'boolean', 'Instamojo Sandbox', false, 1],
                'instamojo_client_id' => ['Instamojo Client ID', 'text', NULL, true],
                'instamojo_client_secret' => ['Instamojo Client Secret', 'text', NULL, true],
                'instamojo_sandbox_client_id' => ['Instamojo Sandbox Client ID', 'text', NULL, true],
                'instamojo_sandbox_client_secret' => ['Instamojo Sandbox Client Secret', 'text', NULL, true],
                'login_logo' => ['Login Logo', 'file', '', true],
                'homepage_feature_course' => ['Featured Courses', 'textarea', 'Life Gurukul is an application that includes lessons on career, business, self-improvement, yoga & fitness, relationship management, and much more.', true],

                'home_top_category_description' => ['Top Category Description','textarea','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Eget aenean accumsan bibendum gravida maecenas augue elementum et neque. Suspendisse imperdiet.',true],
                'home_top_category_title' => ['Top Category Title','text','Top Category',true],
                'home_about_title' => ['Home About Us Title','text','Life Gurukul is an application that includes lessons',true],
                'home_about_description' => ['Home About Us Description','textarea','On career, business, self-improvement, yoga & fitness, relationship management, and much more. It is an initiative by Sneh Desai who is a Life and Business coach for the last 25 Years. And is now working along with his team for the betterment of society. Programs are designed in such a manner that it covers all the age groups without any prerequisites. All you need is a strong zest to learn new skills for your betterment.',true],
                'home_trusted_by_subheading' => ['Home Trusted By Sub Heading','textarea','100+ Leading Universities And Companies',true],
                'home_blog_heading' => ['Home Blog Heading','text','Top Blogs',true],
                'home_slider_visibility' => ['Home Slider Visibility','checkbox','1,2,3',true],
                'home_featured_courses_visibility' => ['Home Featured Courses Visibility','checkbox','1,2,3',true],
                'home_top_free_courses_visibility' => ['Home Top Free Courses Visibility','checkbox','1,2,3',true],
                'home_course_category_visibility' => ['Home Course Category Visibility','checkbox','1,2,3',true],
                'home_trusted_by_visibility' => ['Home Trusted By Visibility','checkbox','1,2,3',true],
                'home_section_user_love_visibility' => ['Home Section User Love Visibility','checkbox','1,2,3',true],
                'home_testimonial_visibility' => ['Home Testimonial Visibility','checkbox','1,2,3',true],
                'home_about_us_visibility' => ['Home About Us Visibility','checkbox','1,2,3',true],
                'home_top_blogs_visibility' => ['Home Top Blogs Visibility','checkbox','1,2,3',true],
                'home_connect_with_visibility' => ['Home Connect With Visibility','checkbox','1,2,3',true],
                'invoice_from' => ['Invoice From','text','SNEH ACADEMIC SERVICES PRIVATE LIMITED',true],
                'YOUTUBE_API_KEY' => ['Youtube Api Key','text','',true],
                'VIMEO_CLIENT' => ['Vimeo Client','text','',true],
                'VIMEO_SECRET' => ['Vimeo Secret','text','',true],
                'VIMEO_ACCESS' => ['Video Access Key','text','',true],
                'VIMEO_ALT_CLIENT' => ['Vimeo Alt Client','text','',true],
                'VIMEO_ALT_SECRET' => ['Vimeo Alt Secret','text','',true],
                'VIMEO_ALT_ACCESS' => ['Vimeo Alt Access','text','',true],
                'VIMEO_VERSION' => ['Vimeo Version','text','',true],
                'ANDROID_API_KEY' => ['Android Api Key','text','',true],
                'IOS_API_KEY' => ['Ios Api Key','text','',true],
                //'FCM_SERVER_API_KEY' => ['FCM Server Api Key','text','',true],
                'whats_app_token' => ['Whatsapp App Token','text','EABT5APwknuoBADnx6nJtdPYe0ZBUCCD1tnfP0EcUHogFU0BdykAizneFIbWzzffHa9VlCfqItZA9H8hZA7ORZBc08u6ocy8tusZCtDgXvjZAXc8QIvs2AugLsTeyXG1qixiXl3XP81pRWG0jotiCxfO1pMs7Kc8LINrxzBivZCTMXBNxyqaRK4ZB',true],
                'vdocipher_key' => ['VdoCipher Key','text','gUYkOTNf18ZXxCflbqtlX429zZWbLLaAGDErBO5ftSXQqylDqgc0wkz56s6Mc7vp',true],
                'plivo_auth_id' => ['Plivo Auth Id','text','MAMTIWMZYYZJE1ZGMZN2',true],
                'whatsapp_contact_no' => ['Whatsapp Contact No','text','+91 72111 28282',true],
                'plivo_send_mobile' => ['Plivo Send Mobile','text','918866003372',true],
                'plivo_auth_token' => ['Plivo Auth Token','text','ODhiMmQ2Y2ZkZjkyMTcwZThmNmJlMDI3ZmVhNWUw',true],
                'plivo_auth_id' => ['Plivo Auth Id','text','MAMTIWMZYYZJE1ZGMZN2',true],
                'mail_logo' => ['Mail Logo', 'file', 'front/img/lifegurukul_small_logo.svg', true],
            ];

            foreach($Settings as $Key => $Value) {
                // remove duplication of insert based on Key
                $item = [
                    'key' => $Key,
                    'value' =>  $Value[2],
                    'display_name' => $Value[0],
                    'setting_type' => $Value[1],
                ];
                $Settings =  Settings::updateOrCreate(['key' => $Key], $item);
            }
        }
    }
}

