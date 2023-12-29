
# Installation

Install dependencies using composer:
```
curl -sS getcomposer.org/installer | php && php composer.phar install
```

# Running

To request the question types you'd like the schema for run:
```
./run.php -t=comma,separated,question,types
```

For example, for Multiple Choice and Match List you'd run:
```
./run.php -t=mcq,association
```
Question types codes can be found in Learnosity's documentation [here]("https://reference.learnosity.com/questions-api/questiontypes")

To get schemas for all the question types you can use `all`:
```
./run.php -t=all
```

## The run

If all is going according to plan you should see something like this:
```
Downloading Question Data... Done.

Processing: mcq
Processing: orderlist

----------------------------------
Saved schema to inferedSchema.json
----------------------------------
```

The initial data download can take some time.

## The result

If the run succeeded you should be able to find a file named `inferedSchema.json` in the script's root directory.

If you re-run the script the same file will be overwritten. Create a copy if you're doing question by question.

# Caveat Emptor

This is a first run at creating a JSON schema and this tool does not output a perfectly correct schema. Use it as a guide, but don't expect it tot be perfect, it may have errors in both the properties and the schema's own formatting.