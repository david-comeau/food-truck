# Food Truck App

This Symfony console CLI application searches and lists San Francisco, CA food truck data found at https://data.sfgov.org/resource/rqzj-sfat.json

Please read and follow terms of use found here https://www.sf.gov/reports/april-2017/datasf-terms-use

## Prerequisites

Ensure Docker and Git are installed on your local machine.
 - Docker -> https://docs.docker.com/get-started/get-docker/
 - Git -> https://git-scm.com/downloads

## Getting Started

### Building the Docker Image

1. Clone this repository:
   ```
   git clone https://github.com/your-username/food-truck.git && cd food-truck
   ```

2. Build the Docker image:
   ```
   docker build -t food-truck .
   ```

### Running the Application

Have Docker running on your machine.

Run the following Docker command in the root /food-truck/ directory to use the food truck app:

```
docker run --rm -it food-truck app:food-truck
```

The food truck app will prompt you for input.

### Choosing the Data Source

Choose to fetch data via the API endpoint, or use the locally included test data.
 - **Default**: Press enter with no input to use the API endpoint.

 ### Searching

Search is tokenized, and requires all search terms and phrases to be found in either the applicant name and/or the food items.
 - Separate multiple search terms with a space.
 - Quote a phrase (single or double) to require the complete phrase to be present.
 - **Default**: Press enter with no input to list all food trucks.

 ### Viewing Results

 Results are displayed in groups of 5. You must press the enter key to view the next group of results.
  - Hold down the enter key to progress quickly through all groups of results.
  - Hit q and press enter to stop viewing results and quit.

## Future Functionality

Due to the constraints of this exercise, this application has limited functionality. Further functionality could include:
 - Log aggregation.
 - Log user actions.
 - More unit test cases.
 - Code coverage with Xdebug.
 - Testing Docker & Git in different environments (only tested Mac OS).
 - More robust search operators (negatives, include/omit fields, etc...) and user input validation.
 - Caching API response.
 - Location based sorting.
 - Web based interface.
 - User login.
 - Saving favorite food trucks.
 - Rating food trucks.

## 3rd Party Data Risk

This application uses data from a 3rd party, and loads the entire dataset into memory at once.

While this application is currently functional and performant, this could change if the size of the dataset or the compute/memory size changes. To handle a larger dataset, a search service such as AWS CloudSearch, Solr, etc... could be implemented.

This dataset could also be compromised, or disappear entirely.

Further work to mitigate these risks would be needed.

## Github Actions Pipeline

The following must pass before merging with master:
 - Linting using PHP_CodeSniffer PSR-12 Extended Coding Style
 - Testing using PHPUnit
 - Static Analysis using PHPStan
 - Symfony Security Check using Symfony CLI

 See `.github/workflows/php-tests.yml` for more info.

### Linting

PHP_CodeSniffer ensures code follows PSR-12 Extended Coding Style.

Use the following Docker command to run PHP_CodeSniffer:

```
docker run -it --rm --entrypoint composer food-truck run-script cs
```

Use the following Docker command to run PHP_CodeSniffer and automatically fix any code issues:

```
docker run -it --rm --entrypoint composer food-truck run-script cs-fix
```

### Testing

PHPUnit is used for testing.

Use the following Docker command to run PHPUnit on all tests in /tests/:

```
docker run -it --rm --entrypoint composer food-truck run-script test
```

### Static Analysis

PHPStan is used for static analysis.

Use the following Docker command to run PHPStan:

```
docker run -it --rm --entrypoint composer food-truck run-script phpstan
```

### Symfony Security Check

Symfony security check is used to check for known security vulnerabilities in 3rd party dependencies.

Use the following Docker command to run the Symfony security check:

```
docker run -it --rm --entrypoint composer food-truck run-script security-check
```

## Development

This application was written using Symfony 7.1 running on PHP 8.3.

### Accessing the Container

If you need to access the container for development or debugging purposes, you can use the following command:

```
docker run -it --rm --entrypoint /bin/bash food-truck
```

This will give you a bash shell inside the container.

Logs found in `var/log`

## Configuration

The application uses a local JSON file for test data. You can find this file at:

```
tests/Fixtures/food_trucks.json
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License.

See the `LICENSE` file for details.
