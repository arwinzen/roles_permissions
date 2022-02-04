<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index()
    {
//        $articles = Article::all();

        // when do we use compact?
//        return view('articles.index', compact('articles'))

        return view('articles.index', [
            'articles' => Article::with('user' )->get()
        ]);
    }

    public function create()
    {

        return view('articles.create', [
            'categories' => Category::all()
        ]);
    }

    public function store(Request $request)
    {
        $attributes = $request->validate([
            'title' => 'required|string',
            'full_text' => 'required|string',
            'category_id' => 'required|integer',
        ]);

        $attributes['user_id'] = auth()->id();
        // only store attribute for published_at if authenticated user is an admin or a publisher
        // additionally populate column only if checkbox is selected
        $attributes['published_at'] = (auth()->user()->is_admin || auth()->user()->is_publisher)
                                        && $request->input('published') ? now() : null;

        Article::create($attributes);

        return redirect()->route('articles.index');

//        dd($attributes);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function show(Article $article)
    {
        //
    }

    public function edit(Article $article)
    {
        $categories = Category::all();
        return view('articles.edit', [
            'article' => $article,
            'categories' => $categories
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Article $article)
    {
        $attributes = $request->validate([
            'title' => 'required|string',
            'full_text' => 'required|string',
            'category_id' => 'required|integer',
        ]);

        $attributes['published_at'] = (auth()->user()->is_admin || auth()->user()->is_publisher)
                                     && $request->input('published') ? now() : null;

        $article->update($attributes);

        return redirect()->route('articles.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function destroy(Article $article)
    {
        $article->delete();

        return redirect()->route('articles.index');
    }
}
