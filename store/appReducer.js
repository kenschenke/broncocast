import C from '../constants';
import { combineReducers } from 'redux';
import { DuxFormReducer } from 'duxform';
import { DuxTableReducer } from 'duxtable';
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
    register: dataReducer(C.SET_REGISTER_DATA),
    mybroadcasts: dataReducer(C.SET_MYBROADCASTS_DATA),
    admin_org: dataReducer(C.SET_ADMIN_ORG),
    admin_users: dataReducer(C.SET_ADMIN_USERS_DATA),
    admin_groups: dataReducer(C.SET_ADMIN_GROUPS_DATA),
    admin_broadcasts: dataReducer(C.SET_ADMIN_BROADCASTS_DATA),
    admin_newbroadcast: dataReducer(C.SET_ADMIN_NEWBROADCAST_DATA),
    system_orgs: dataReducer(C.SET_SYSTEM_ORGS_DATA),
    system_users: dataReducer(C.SET_SYSTEM_USERS_DATA),
    forms: DuxFormReducer,
    duxtable: DuxTableReducer
});
