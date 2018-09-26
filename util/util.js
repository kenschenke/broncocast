export const formatPhoneNumber = value => {
    if (value.length !== 10 || value.search(/[^0-9]/) !== -1) {
        return value;
    }

    return `(${value.substr(0,3)}) ${value.substr(3,3)}-${value.substr(6)}`;
};
