# Reen

_PHP framework designed to be just backend for javascript SPA frameworks. Now, it is in sketch phase. I am collecting ideas what it should to know. It is work in progress and it is at the beginning now (almost no code :D). And I am looking for contributors/co-maintainers for this project. Check my ideas below and if it interests you, <a href="#contributing">let's get together</a> to create something awesome!_

## NOTE

I have studied a lot and have changed many opinions. With this knowledge I hope the framework will be better than this. So I am going to write new draft with these changes included. If are you interested in, feel free to contact me - petr.nevyhosteny(at)gmail.com. And please note that I am trying to make something different from other frameworks.

## What?

Reen will be another PHP framework. Why should we have another PHP framework if we have a plenty of them? Because Reen will have a different approach. It will be designed as backend for javascript SPA applications. So, no MVC architecture or something like this. We don't need neither View or Controller, because all will be done in our javascript SPA framework. We need only something like model and maybe some server related services. Do you understand?

## Why?

I want to be a javascript developer, mainly focused on SPA applications. And I need a easy-to-create backend model with some key features. Yes, I could make my backend in an existing PHP framework and just leave their controllers, views nad so on. But I think, better solution is framework designed to my purpose. It will contains only what I need. It's cool, isn't it? I have looking for framework whoch would satisfy my requirements, but without success (perhaps, I have looking for badly, could you recommend one?). And why PHP? Why not to use NodeJS if I want to be a javascript developer? I like NodeJS and I work with it, but it is not as widespread as PHP is. And famous proverb says: "As many programming languages you know so many times you are a programmer".

## Why not?

Let's be objective. SPA applications could be risky. What if the user have javascript disabled or his browser is mush old to satisfy your SPA framework minimal requirements? Unfortunately, then your whole application wouldn't work at all.

---

# Changelog

### 0.0.0 pre-alpha - Enthusiastic novice

First sketch of the project. There are exposed basic thoughts.

---

## Some of key features

- Write only resources and services, no controllers, views, templates, URL routing and so on
- Always serve index file and let a frontend framework to do the all the work
- Allow access to server methods by REST API and add augmented support for it (see more below)
- Define resources with SCRUD paradigm (search, create, read, update, delete)
- Storage (database) abstraction
- Define authorization rules by policies (write what would be allowed with "this" context)
- As the framework will be meant as only backend for SPA framework, the default format of responses will be JSON, not HTML (of course, files will be sent normally)
- Abstracted Request and Response object, use PHP superglobals nevermore

### Augmented RESTful resourcing

My idea is following:

```php
class Orders extends Resource {
    // SCRUD actions will be automatically binded to related methods
    public function search() {
        /*
            if the requested URL will be GET /orders - ...storage('db')->table
            will return whole table from database
            
            if the requested URL will be GET /users/123/orders -
            ...storage('db')->table will return only user #123 related orders
            (the user #123 will be got from Users resource read method)
         */
        return $this->context->storage('db')->table('orders');
    }
    
    public function create() {
        /*
            if the requested URL will be POST /orders - this method just inserts
            new order to database
            
            if the requested URL will be POST /users/123/orders - this method
            inserts new order to database and call update method of Users
            resource with id 123 and make relationship between the user and the
            order automatically
         */
        return $this->context->storage('db')->table('orders')
            ->insert($this->request->params());
    }
    
    public function delete($id) {
        /*
            if the requested URL will be DELETE /orders/456 - this method just
            deletes the record from database
            
            if the requested URL will be DELETE /users/123/orders/456 - this
            method deletes the order from database and also from user #123
            using Users resource again
         */
        return $this->context->storage('db')->table('orders')->delete($id);
    }
}
```

See the benefit? Reen will abstract you REST calling and, with the same line of code, you can cover many situations. And if you follow conventions, you don't need to specify schema of a resource (but this will be also supported).

### Storage abstraction

I know this is not unique feature, I don't say it at all. But see my conception:

```php
class Users extends Resource {
    // URL for this custom action has to be this: /users/:id/profile_picture
    public function profile_picture($id) {
        $user = $this->context->storage('db')->table('users')->findById($id);
        return $this->context->storage('files')->get($user->profile_picture_url);
        // I know, we should return only the URL, but this is just for example
    }
    
    public function something() {
        /*
            we have more storages than one, this is defined in config file
            so, we can use more than one databases (for example MySQL and Redis)
            just like this: ...storage('mysql')... and ...storage('redis')...
            
            but it also allows us to develop app when we don't have an access
            to database yet, in config define storage db as files and Reen file
            adapter will save your files to server, and later change db to
            actual database and you don't have to change even one line of code
         */
    }
}
```

And storage should determine "read-only" and "changing" actions. The reason is that we can return cached value of a query when we know nothing has been changed. And when change occurs, clear (or update) the cache.

### Policies (ACL)

I think "policy" approach is more flexible than "roles" (_note: maybe I understand roles authorization wrong_). So, how to do authorization in Reen? We have Request object, let's check some values and then, decide what is allowed. See:

```php
class HasAllowedSize extends IPolicy {
    public static function execute($id = null) {
        if ($this->context->request->file('profile_picture')->getSize() > 2000000) {
            $this->context->response->sendForbidden();
            //$this->context->response->send('@403');
            return false;
        }
        
        return true;
    }
}
```

Declarative way of defining permissions:

```php
//in config.php

//...

'acl' => array(
    'services' => array(
        'profile' => array(
            '*' => false, //default true
            'uploadImage' => array('HasAllowedSize')
        )
    )
);

//...
```

### Abstracted Request and Response

Again, this is nothing new under the sun. But it's pretty useful. Let's look at some features of Response object:

```php
//somewhere in the code:

// default is json, could be also "html", "mimetype", "xml", "text"
// the advantage of this approach is clear - pass PHP data (arrays, ...)
// and let Response to parse the data into specified format automatically
$this->context->response->setFormat('xml');

// the Response is also useful for sending output by pieces
class SomeStructure {
    public $first;
    public $second;
    public $third;

    public function __construct($first, $second) {
        $this->first = $first;
        $this->second = $second;
        $this->third = array('lang' => 'PHP', 'server' => 'apache');
    }
    
    public function itsMethod() {
        return 'Foo';
    }
}

$this->context->response->send('name', 'Reen');
$this->context->response->send('features', 'Resource based PHP framework');
$this->context->response->send('features', 'Augmented REST API support');
$this->context->response->send('features', 'ACL based authorization');
$this->context->response->send('structure', new SomeStructure('one', array(1, 'two')));

/*
    sended response will look as follows:
    
    __json__
    
    {
        "name" : "Reen",
        "features" : [
            "Resource based PHP framework",
            "Augmented REST API support",
            "ACL based authorization"
        ],
        "structure" : {
            "first" : "one",
            "second" : [
                1,
                "two"
            ],
            "third" : {
                "lang" : "PHP",
                "server" : "apache"
            }
        }
    }
    
    __xml__
    
    <?xml version="1.0" encoding="UTF-8" ?>
    <root>
        <name>Reen</name>
        <features>
            <item>Resource based PHP framework</item>
            <item>Augmented REST API support</item>
            <item>ACL based authorization</item>
        </features>
        <structure>
            <first>one</first>
            <second>
                <item>1</item>
                <item>two</item>
            </second>
            <third>
                <lang>PHP</lang>
                <server>apache</server>
            </third>
        </structure>
    </root>
 */

```

## Short notes

- Allow namespacing for URLs (for collision avoidance between front-end routes and REST routes)

---

<a name="contributing"></a>
# Contributing

If I am doing it alone, it will be the long run. And I am not so great PHP developer to manage it. I will make mistakes and wrong decisions. If you are interested in this project, I would appreciate your contribution and you could be a co-maintainer as well. I hope, together, we will make something wonderful!

## How to contribute

Everything is in progress, nothing is stable yet. Everything can change (even the name of this project might be different). Let's discuss the API, let's discuss the architecture, let's discuss features.

You can contribute via Github, or - if you want to cooperate on this project - you can contact me via e-mail `petr.nevyhosteny<at>gmail.com`. I have also created google group [Reen](https://groups.google.com/forum/#!forum/reen-dev). Feel free to contribute, I will really appreciate it!

# License

This project is MIT licensed.