<?php

namespace App\Models;

use GuzzleHttp\Psr7\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_content',
        'user_id',
        'payment_type',
        'price',
        'garages',
        'bedrooms',
        'bathrooms',
        'area',
        'location',
        'address',
        'title',
        'year_built',
        'type',
        'master_bedroom',
        'bedroom_two',
        'other_room',
        'living_room',
        'kitchen',
        'dining_room',
        'half_baths',
        'full_baths',
    ];

    public static function getPosts()
    {
        $posts = Post::leftJoin('gallaries', function ($q) {
            $q->on('posts.id', '=', 'gallaries.imageable_id')
                ->where('gallaries.imageable_type', 'App\Models\Post')
                ->whereNull('deleted_at');
        })
            ->join('users', 'posts.user_id', '=', 'users.id')
            ->select(
                'posts.*',
                'users.name as user_name', 
                DB::raw('GROUP_CONCAT(gallaries.name) as images')
            )
            ->groupBy(
                'posts.id',
                'posts.post_content',
                'posts.created_at',
                'posts.updated_at',
                'posts.user_id',
                'posts.payment_type',
                'posts.price',
                'posts.garages',
                'posts.bedrooms',
                'posts.bathrooms',
                'posts.area',
                'posts.location',
                'posts.address',
                'posts.title',
                'posts.year_built',
                'posts.type',
                'posts.master_bedroom',
                'posts.bedroom_two',
                'posts.other_room',
                'posts.living_room',
                'posts.kitchen',
                'posts.dining_room',
                'posts.half_baths',
                'posts.full_baths',
                'users.name'
            )
            ->get();

        foreach ($posts as $post) {
            $post->images = $post->images ? explode(',', $post->images) : [];
        }

        return $posts;
    }

    public static function showCreate()
    {
        return view('posts.upsert');
    }

    public static function createPost($request)
    {
        $post = Post::create([
            'post_content' => $request->input('post_content'),
            'title' => $request->input('title'),
            'location' => $request->input('location'), 
            'address' => $request->input('address'),
            'area' => $request->input('area'),
            'garages' => $request->input('garages'),
            'bedrooms' => $request->input('bedrooms'),
            'bathrooms' => $request->input('bathrooms'),
            'price' => $request->input('price'),
            'payment_type' => 1,
            'user_id' => Auth::user()->id,
            'year_built' => $request->input('year_built'),
            'type' => $request->input('type'),
            'master_bedroom' => $request->input('master_bedroom'),
            'bedroom_two' => $request->input('bedroom_two'), 
            'other_room' => $request->input('other_room'),
            'living_room' => $request->input('living_room'),
            'kitchen' => $request->input('kitchen'),
            'dining_room' => $request->input('dining_room'),
            'half_baths' => $request->input('half_baths'),
            'full_baths' => $request->input('full_baths')
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {

                $filename = time() . '_' . $image->getClientOriginalName();

                // Store image in public/postImages directory
                if (!file_exists(public_path('post_images'))) {
                    mkdir(public_path('post_images'), 0777, true);
                }
                $image->move(public_path('post_images'), $filename);

                Gallary::create([
                    'name' => 'post_images/' . $filename,
                    'imageable_id' => $post->id,
                    'imageable_type' => 'App\Models\Post',
                    'use_for' => 'posts'
                ]);
            }
        }

        return redirect()->route('posts');
    }

    public static function deletePost($request)
    {
        // Find the post
        $post = Post::find($request->post_id);

        if ($post) {
            // Get associated gallery images
            $galleryImages = Gallary::where('imageable_id', $request->post_id)  // Changed from $request->post
                ->where('imageable_type', 'App\Models\Post')
                ->get();

            // Delete physical image files and gallery records
            foreach ($galleryImages as $image) {
                if (file_exists(public_path($image->name))) {
                    unlink(public_path($image->name));
                }
                $image->delete();
            }

            // Delete the post
            $post->delete();

            return redirect()->route('posts');
        }

        return redirect()->route('posts');
    }


    public static function updatePost($request)
    {
        $post = Post::find($request->post_id);
        if ($post) {
            $post->post_content = $request->input('post_content');
            $post->title = $request->input('title');
            $post->location = $request->input('location');
            $post->address = $request->input('address');
            $post->area = $request->input('area');
            $post->garages = $request->input('garages');
            $post->bedrooms = $request->input('bedrooms');
            $post->bathrooms = $request->input('bathrooms');
            $post->price = $request->input('price');
            $post->payment_type = 1;
            $post->year_built = $request->input('year_built');
            $post->type = $request->input('type');
            $post->master_bedroom = $request->input('master_bedroom');
            $post->bedroom_two = $request->input('bedroom_two');
            $post->other_room = $request->input('other_room');
            $post->living_room = $request->input('living_room');
            $post->kitchen = $request->input('kitchen');
            $post->dining_room = $request->input('dining_room');
            $post->half_baths = $request->input('half_baths');
            $post->full_baths = $request->input('full_baths');            
            
            $post->save();

            $remainingImageIds = $request->input('remaining_images', []);

            $post->gallaryImages()
                ->whereNotIn('id', $remainingImageIds)
                ->get()
                ->each(function ($image) {
                    // Remove the file from storage
                    if (file_exists(public_path($image->name))) {
                        unlink(public_path($image->name));
                        $image->delete();
                    }
                });

            // Add new images if present
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $filename = time() . '_' . $image->getClientOriginalName();

                    // Store image in public/postImages directory
                    if (!file_exists(public_path('post_images'))) {
                        mkdir(public_path('post_images'), 0777, true);
                    }
                    $image->move(public_path('post_images'), $filename);

                    Gallary::create([
                        'name' => 'post_images/' . $filename,
                        'imageable_id' => $post->id,
                        'imageable_type' => 'App\Models\Post',
                        'use_for' => 'posts'
                    ]);
                }
            }

            return redirect()->route('posts');
        }
        return false;
    }

    // Gallery images relationship
    public function gallaryImages()
    {
        return $this->morphMany(Gallary::class, 'imageable');
    }
}
