Introduction
============

This contribution style guide is largely based upon the
[Drupal Coding Standards](https://www.drupal.org/coding-standards), with slight
modifications.  Please follow these guidelines as closely as possible when
submitting code to any of this organization's repositories.  You must also note
that by committing code, either by pull request or explicit organization
permissions, you agree that your code be licensed under the parent license of
the Modfwango project.

Table of Contents
=================

* [Indenting and Whitespace](#indenting-and-whitespace)
* [Operators](#operators)
* [Casting](#casting)
* [Control Structures](#control-structures)
* [Line Length and Wrapping](#line-length-and-wrapping)
* [Function Calls](#function-calls)
* [Function Declarations](#function-declarations)
* [Class Constructor Calls](#class-constructor-calls)
* [Arrays](#arrays)
* [String Concatenations](#string-concatenations)
* [Comments](#comments)
* [Including Code](#including-code)
* [PHP Code Tags](#php-code-tags)
* [Naming Conventions](#naming-conventions)

Contribute
==========

#### Indenting and Whitespace

Use an indent of 2 spaces, with no tabs.

Lines should have no trailing whitespace at the end.

Files should be formatted with `\n` as the line ending (Unix line endings), not
`\r\n` (Windows line endings).

All text files should end in a single newline (`\n`). This avoids the verbose
"\ No newline at end of file" patch warning and makes patches easier to read
since it's clearer what is being changed when lines are added to the end of a
file.

#### Operators

All binary operators (operators that come between two values), such as `+, -, =,
!=, ==, >`, etc. should have a space before and after the operator, for
readability. For example, an assignment should be formatted as `$foo = $bar;`
rather than `$foo=$bar;`. Unary operators (operators that operate on only one
value), such as ++, should not have a space between the operator and the
variable or number they are operating on.

#### Casting

Put no space between the (type) and the $variable in a cast: `(int)$mynumber`.

#### Control Structures

Control structures include if, for, while, switch *(please never use these)*,
etc. Here is a sample if statement, since it is the most complicated of them:

```php
if (condition1 || condition2) {
  action1;
}
elseif (condition3 && condition4) {
  action2;
}
else {
  defaultaction;
}
```

*Note: Don't use "else if" -- always use elseif.*

Control statements should have one space between the control keyword and opening
parenthesis, to distinguish them from function calls.

Always use curly braces even in situations where they are technically optional.
Having them increases readability and decreases the likelihood of logic errors
being introduced when new lines are added. The opening curly should be on the
same line as the opening statement, preceded by one space. The closing curly
should be on a line by itself and indented to the same level as the opening
statement.

For do-while statements:

```php
do {
  actions;
} while ($condition);
```

#### Line Length and Wrapping

* In general, all lines of code should not be longer than 80 chars.
* When you have to wrap lines, the continuing line should be indented 2 spaces.
* For conditions which exceed 80 characters, it is recommended practice to split
out and prepare the conditions separately, which also permits documenting the
underlying reasons for the conditions:

```php
// Key is only valid if it matches the current user's ID, as otherwise other
// users could access any user's things
$is_valid_user = (isset($key) && !empty($user->uid) && $key == $user->uid);

// IP must match the cache to prevent session spoofing
$is_valid_cache = (isset($user->cache) ? $user->cache == ip_address() : FALSE);

// Alternatively, if the request query parameter is in the future, then it
// is always valid, because the galaxy will implode and collapse anyway
$is_valid_query = $is_valid_cache || (isset($value) && $value >= time());

if ($is_valid_user || $is_valid_query) {
  ...
}
```

#### Function Calls

Functions should be called with no spaces between the function name, the opening
parenthesis, and the first parameter; spaces between commas and each parameter,
and no space between the last parameter, the closing parenthesis, and the
semicolon. Here's an example:

```php
$var = foo($bar, $baz, $quux);
```

As displayed above, there should be one space on either side of an equals sign
used to assign the return value of a function to a variable.

#### Function Declarations

```php
function funstuff_system($field) {
  $system["description"] = t("This module inserts funny text into posts ".
    "randomly.");
  return $system[$field];
}
```

Arguments with default values go at the end of the argument list.

#### Class Constructor Calls

When calling class constructors with no arguments, always include parentheses:

```php
$foo = new MyClassName();
```

This is to maintain consistency with constructors that have arguments:

```php
$foo = new MyClassName($arg1, $arg2);
```

#### Arrays

Arrays should be formatted with a space separating each element (after the
comma), and spaces around the => key association operator, if applicable:

```php
$some_array = array('hello', 'world', 'foo' => 'bar');
```

Note that if the line declaring an array spans longer than 80 characters, each
element should be broken into its own line, and indented one level:

```php
$form['title'] = array(
  '#type' => 'textfield',
  '#title' => t('Title'),
  '#size' => 60,
  '#maxlength' => 128,
  '#description' => t('The title of your node.')
);
```

Note the lack of a comma at the end of the last array element! Trailing commas
are strictly prohibited.

#### String Concatenations

Never use a space between the dot and the concatenated parts as such:

```php
$string = 'Foo' . $bar;
$string = $bar . 'foo';
$string = bar() . 'foo';
$string = 'foo' . 'bar';
```

Never place variables inside of a string as such:

```php
$string = "Foo $bar";
```

When using the concatenating assignment operator ('.='), use a space on each
side as with the assignment operator:

```php
$string .= 'Foo';
$string .= $bar;
$string .= baz();
```

#### Comments

Please use single line comments in all cases.  When making inline documentation,
use proper punctuation, grammar, and spelling as often as possible.  Also,
truncate the trailing punctuation on the last line/sentence of a comment.

```php
// Set the default timezone to America/Chicago; modules can temporarily set
// their own timezone configuration if they wish.  I'll get around to adding
// a configuration parameter for this one day
```

#### Including Code

Never include or require code within a module.  In other places, such as patches
to the core, always use the `_once` function versions of require and include.

#### PHP Code Tags

Always use `<?php ?>` to delimit PHP code, not the shorthand, `<? ?>`.

#### Naming Conventions

Use common sense here; name your identifiers according to their purpose, and
always use camel case. When naming constants, use all caps, and prepend and
append two underscores.  Example:  `__PROJECTROOT__`, or `registerForEvent`.

In the case of module files and names, use camel case, but with a capital
initial character.  Example:  `ConnectionLoopEndEvent`.
