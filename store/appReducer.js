import C from '../constants';
import { combineReducers } from 'redux';
import { DuxFormReducer } from 'duxform';
import objectAssign from 'object-assign';

export const dataReducer = type => (state={}, action) => {
    if (type === action.type) {
        let newState = objectAssign({}, state);
        return objectAssign(newState, action.payload);
    } else {
        return state;
    }
};

export default combineReducers({
    router: dataReducer(C.SET_ROUTER_DATA),
    profile_contacts: dataReducer(C.SET_PROFILE_CONTACTS_DATA),
    profile_pwd: dataReducer(C.SET_PROFILE_PWD_DATA),
    profile_orgs: dataReducer(C.SET_PROFILE_ORGS_DATA),
    forms: DuxFormReducer
});
