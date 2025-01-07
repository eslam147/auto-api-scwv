
If no version is specified, it defaults to `http://your-domain.com/api`.

---

## **Documention**

# Laravel Auto API Single Controller 

## **Overview**

This package when that you installed that it can write one path and one controller then return the view to the web and from the same controller to restore and re-register the json. This package will write one source code for the web and the API at the same time, you do not need to write one source code for the web and another for the API and you don't need to write any route in file api.php and you don't need to write any custom controller for api.  This package saves you from writing the source code because you will write single source code that works on the web and works on applications by one source code only

## ** installtion **

<pre>
    <code class="bash">
        composer require laravel/auto-api-scwv
    </code>
</pre>

## ** Settings **
if you want to set version for api

you can do this by .env

<pre>
    <code class=".env">
        API_VERSION= v1.0
    </code>
</pre>

if you want to rename key api

in .env file you can rename key api

<pre>
    <code class=".env">
        API_NAME= new_name
    </code>
</pre>
if you want to change auth type api

in .env file you can change auth

<pre>
    <code class=".env">
        AUTH_API= auth:api
    </code>
</pre>

if you want to enable or disable clear cache route by default is true

in .env file you can do this

<pre>
    <code class=".env">
        CLEAR_CACHE_ON_BOOT= false
    </code>
</pre>
if you want to gitignore any guard

it is simplly add this to auth.php file
<pre>
    <code class="auth">
        'gitignore' => [
            'api',
            'sanctum',
            'admin',
        ]
    </code>
</pre>
### **How To Use?**
In controller
<pre>
<code class="php">
namespace App\Http\Controllers;

class ExampleController extends Controller
{
    public function show()
    {
        return render('example'); // Use `render` instead of `view`
    }

}
</code>
</pre>

### **In blade File**

<pre>
    <code class="blade">
    href="{{ routeApi('about') }}
    href="{{ routeApi('product', ['id' => $id]) }}
    </code>
</pre>

<pre>
    <code class="blade">
       action="{{ routeApi('login') }}"
    </code>
</pre>


### **Custom Artisan Command Example**

Create a new API request class with the command:

<pre>
    <code class="bash">php artisan make:requestapi CreateUserRequest</code>
</pre>

This generates the file in the `app/Http/Requests` directory, optimized for API validation.

---

## **Contribution**

Feel free to contribute by submitting a pull request or reporting issues. All contributions are welcome and appreciated!

---

## **License**

This package is open-sourced software licensed under the [MIT license](LICENSE).
