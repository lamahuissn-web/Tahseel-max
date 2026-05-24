<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\ContactRequest;
use App\Http\Requests\Site\StudentRegistrationRequest;
use App\Http\Resources\Site\AboutResource;
use App\Http\Resources\Site\BannerResource;
use App\Http\Resources\Site\BlogResource;
use App\Http\Resources\Site\EventResource;
use App\Http\Resources\Site\PhotoResource;
use App\Http\Resources\Site\VideoResource;
use App\Models\Site\SiteAbout;
use App\Models\Site\SiteBanner;
use App\Models\Site\SiteBlog;
use App\Models\Site\SiteContact;
use App\Models\Site\SiteEvent;
use App\Models\Site\SitePhoto;
use App\Models\Site\SiteTeacher;
use App\Models\Site\SiteVideo;
use App\Models\student_registration;
use Illuminate\Http\Request;

class HomeController extends Controller
{

    function index()
    {

        $blogs = $this->prepare_data(BlogResource::collection(SiteBlog::with('images')->limit(6)->get()));
        $events = $this->prepare_data(EventResource::collection(SiteEvent::with('images')->limit(6)->get()));
        $about = $this->prepare_data(AboutResource::collection(SiteAbout::all()));
        $banners = $this->prepare_data(BannerResource::collection(SiteBanner::all()));
//        dd($data);
        return view('web_site.home', compact('banners', 'about', 'events', 'blogs'));
    }

    function about()
    {
        $about = $this->prepare_data(AboutResource::collection(SiteAbout::all()));

    }

    function teacher(Request $request)
    {
        $all = SiteTeacher::paginate(12);
//        $data['teacher'] = $this->prepare_data(TeacherResource::collection($data['all']));
        return view('web_site.teacher', compact('all'))
            ->with('i', ($request->input('page', 1) - 1) * 12);
    }

    function video(Request $request)
    {
        $videos = SiteVideo::paginate(12);
//        $videos = $this->prepare_data(VideoResource::collection($data['all']));
        return view('web_site.videos', compact('videos'))
            ->with('i', ($request->input('page', 1) - 1) * 12);
    }

    function photos(Request $request)
    {
        $photos = SitePhoto::with('images')->paginate(12);
//        $data['photos'] = $this->prepare_data(PhotoResource::collection($data['all']));
        return view('web_site.gallery.gallery', compact('photos'))
            ->with('i', ($request->input('page', 1) - 1) * 12);
    }
    function photosDetails(Request $request, $id)
    {
        $one_data = SitePhoto::with('images')->findorfail($id);
        $one_data = $this->prepare_data(new PhotoResource($one_data));
        $all = SitePhoto::with('images')->limit(12);
//        dd($one_data);
        return view('web_site.gallery.gallery_details', compact('one_data', 'all'));

    }
    function contact_us(Request $request)
    {
        return view('web_site.contact');
    }

    function SaveContact_us(ContactRequest $request)
    {
        try {
            $insert_data = $request->all();

            $inserted_data = SiteContact::create($insert_data);

            toastr()->addSuccess(trans('forms.success'));
            return redirect()->route('contact_us');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }

    }

    function StudentRegistration(Request $request)
    {
        return view('web_site.StudentRegistration');
    }

    function SaveStudentRegistration(StudentRegistrationRequest $request)
    {
        try {
            $insert_data = $request->all();

            $inserted_data = student_registration::create($insert_data);

            toastr()->addSuccess(trans('forms.success'));
            return redirect()->route('StudentRegistration');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }

    }

    function blogs(Request $request)
    {

        $all = SiteBlog::with('images')->paginate(12);
//        $all = $this->prepare_data(BlogResource::collection($data['allData']));
//        dd($data);
        return view('web_site.blogs.blogs', compact('all'))->with('i', ($request->input('page', 1) - 1) * 12);

    }

    function one_blog(Request $request, $id)
    {
        $one_data = SiteBlog::findorfail($id);
        $one_data = $this->prepare_data(new BlogResource($one_data));
        $all = SiteBlog::with('images')->limit(12);
//        dd($one_data);
        return view('web_site.blogs.blog_detail', compact('one_data', 'all'));

    }

    function events(Request $request)
    {
        $all = SiteEvent::with('images')->paginate(3);
//        $all = $this->prepare_data(new EventCollection($data['allData']));
//dd($all,$data['allData']);
        return view('web_site.events.events', compact('all'))->with('i', ($request->input('page', 1) - 1) * 12);

    }

    function one_events(Request $request, $id)
    {
        $one_data = SiteEvent::findorfail($id);
        $one_data = $this->prepare_data(new EventResource($one_data));

//        dd($one_data);
        return view('web_site.events.eventDetails', compact('one_data'));

    }
}
