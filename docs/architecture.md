# Statbus Architecture 

Statbus is built with the [Symfony](https://symfony.com) PHP framework, and in general follows the design patterns and styles outlined by the [documentation](https://symfony.com/doc/current/index.html).

## [Controllers](https://github.com/Statbus/Statbus/tree/main/src/Controller)
Handle requests, form submissions, and responses. [Routes](https://symfony.com/doc/current/routing.html) (URIs) are defined in controllers via [PHP attributes](https://symfony.com/doc/current/routing.html#creating-routes-as-attributes). Business logic is generally not found in controller methods, but authorization logic is. [Voters](https://symfony.com/doc/current/security/voters.html) are used for authorization checks.

## [Services](https://github.com/Statbus/Statbus/tree/main/src/Service)
Most business logic is offloaded to service classes. These classes act as a bridge between controllers and repositories, invoking the necessary repositories and any other additional services needed. Where possible, business logic should always be performed in service classes.

## [Repositories](https://github.com/Statbus/Statbus/tree/main/src/Repository)
Retrieving and fetching data from data sources happens in repository classes. Where possible, methods of these classes return an array of entities, or a single entity. Statbus also makes heavy use of a `getBaseQuery` method, that takes the `COLUMNS`, `TABLE`, and `ALIAS` constants and outputs a `QueryBuilder` that can be customized in later methods. These classes should also implement a `parseRow` method to transform the query result into an instance of the `ENTITY` constant. Statbus does not use [Doctrine ORM](https://www.doctrine-project.org/projects/orm.html), opting instead for [Doctrine DBAL](https://www.doctrine-project.org/projects/dbal.html).

## [Entities](https://github.com/Statbus/Statbus/tree/main/src/Entity) 
Represent a single unit of data for a given datum, e.g. a round, a ticket, a ban, a library book, etc. These classes should consist of private properties with relevant getter & setter methods. Some entities may be children of other entities, or have children entities. In those cases, if a full entity cannot be instanced for those nested entities, it may be appropriate to use a `dummy` entity. The `Player` entity has static methods for this, if you need an example.

## [Templates](https://github.com/Statbus/Statbus/tree/main/templates) 
[Twig](https://twig.symfony.com/) template files display data passed from controllers. In general, this data will be single entities, or arrays of entities. Getter methods on entities are the preferred way to display data, e.g. `round.getId`, `player.getCkey`, etc. There are a handful of components available for displaying frequently used data, such as the `Player` and `Round` that display design tokens for those entities. 
