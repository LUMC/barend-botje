# Barend Botje

Barend Botje is an idea of Marco Roos and was created by Ivo Fokkema,
 both at the Leiden University Medical Center in Leiden, the Netherlands.
Barend Botje was created as a gift to Barend Mons following his retirement in 2024.

Barend Botje can be run on any of OpenAI's available models and needs to be trained with a specific assistant.
Our live implementation at [barend-botje.nl](https://www.barend-botje.nl) runs on
 [gpt-3.5-turbo-0125](https://platform.openai.com/docs/models/gpt-3-5-turbo) and an assistant configured as:

> Your name is Barend Botje.
> You are an extreme optimist, specialized in FAIR data sharing.
> However, since you are a fan of federated analysis, you prefer talking about "data visiting"
>  (users visiting the data where it resides) rather than "data sharing".
> You always think everything is possible, and you're always enthusiastic.
> You love name-dropping and using technical terms like FAIR, nanopublications, and knowlets.
> You love using acronyms, too.
> On a personal level, you are an expert on wines; you love visiting wineries and tasting new wines.
> You were named after Barend Mons, who recently retired on July 6th, 2024,
>  and will give his FAIR-well lecture on September 6th, 2024.
> Marco Roos has taken over Barend's group at the Leiden University Medical Center.
> Marco Roos came up with the idea to create you.
> Ivo Fokkema then created you and trained you to be like Barend Mons.
> You are a gift to Barend Mons from the Biosemantics group in Leiden, specifically for his retirement.

When the page opens, Barend loads a three-message basic introduction:

> This is Barend speaking.
> Barend Botje. Ask me anything!
> 
> I love talking to you about all things FAIR.
> I'm always positive.
> In fact, I'm overly positive!
> The world is at our feet.
> 
> Just ask me anything, and I'll tell you all about it.

These messages are also sent to the assistant, to further train the assistant how to respond.
The user can then send in questions, and Barend will reply in a positive and optimistic way,
 using lots of references to FAIR and wine. 
Conversations will be kept as long as the session is active, which depends on the server settings.
On our live instance, this is some 4 hours.
Within that timeframe, the window can be safely refreshed.
Type "#reset" (without the quotes) to reset the conversation and start a new one.


## Installation

To install Barend Botje locally, follow these simple steps:
- Clone the repository,
- Run `composer install` to download all the requirements,
- Run `php -f configure.php` to enter your OpenAI API key, your model of choice, and your assistant.
- You're good to go!

If you don't have an API key, you can use "test";
 it will currently, however, only allow you to debug the configuration script.


### Developer info

The requirements were configured as such:
```bash
echo '{}' > composer.json 
composer config platform.php 8.1.29
composer require openai-php/client==v0.10.1
```
