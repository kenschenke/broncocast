import { dataReducer } from '../../store/appReducer';

describe('dataReducer tests', () => {
    test('type does not match', () => {
        const expectedType = 'EXPECTED';
        const testedType = 'TESTED';

        const reducer = dataReducer(expectedType);
        const expectedState = {empty: 'empty'};
        const testedState = {empty: 'empty'};

        const actualState = reducer(testedState, {
            type: testedType,
            payload: {value1: 1}
        });
        expect(actualState).toEqual(expectedState);
    });

    test('scalar value does not exist', () => {
        const actionType = 'TYPE';
        const startingState = {
            value1: 'VALUE1',
            value2: 2
        };
        const expectedState = {
            value1: 'VALUE1',
            value2: 2,
            value3: 3
        };

        const before = JSON.stringify(startingState);
        const reducer = dataReducer(actionType);
        const endingState = reducer(startingState, {
            type: actionType,
            payload: {value3: 3}
        });
        const after = JSON.stringify(startingState);
        expect(before).toEqual(after);
        expect(endingState).toEqual(expectedState);
    });

    test('scalar value already exists', () => {
        const actionType = 'TYPE';
        const startingState = {
            value1: 'VALUE1',
            value2: 2
        };
        const expectedState = {
            value1: 1,
            value2: 2
        };

        const before = JSON.stringify(startingState);
        const reducer = dataReducer(actionType);
        const endingState = reducer(startingState, {
            type: actionType,
            payload: {value1: 1}
        });
        const after = JSON.stringify(startingState);
        expect(before).toEqual(after);
        expect(endingState).toEqual(expectedState);
    });

    test('array value does not exist', () => {
        const actionType = 'TYPE';
        const startingState = {
            value1: 'VALUE1',
            value2: 2
        };
        const expectedState = {
            value1: 'VALUE1',
            value2: 2,
            array3: [1, 2, 3]
        };

        const before = JSON.stringify(startingState);
        const reducer = dataReducer(actionType);
        const endingState = reducer(startingState, {
            type: actionType,
            payload: {array3: [1, 2, 3]}
        });
        const after = JSON.stringify(startingState);
        expect(before).toEqual(after);
        expect(endingState).toEqual(expectedState);
    });

    test('array value already exists', () => {
        const actionType = 'TYPE';
        const startingState = {
            value1: 'VALUE1',
            value2: 2,
            array3: [1, 2]
        };
        const expectedState = {
            value1: 'VALUE1',
            value2: 2,
            array3: [1, 2, 3]
        };

        const before = JSON.stringify(startingState);
        const reducer = dataReducer(actionType);
        const endingState = reducer(startingState, {
            type: actionType,
            payload: {array3: [1, 2, 3]}
        });
        const after = JSON.stringify(startingState);
        expect(before).toEqual(after);
        expect(endingState).toEqual(expectedState);
    });
});
