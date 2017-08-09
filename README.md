# Validator example usage


    public function dummy($params)
    {
        // Validate params
        $params = Validator::validate($params, [
            'name'     => ['required','string','minLength:6'],
            'phone'    => ['required','string','minLength:10'],
            'email'    => ['required','email'],
            'raw'      => ['string|no-sanitize'],
        ]);

        // Other code
    }
