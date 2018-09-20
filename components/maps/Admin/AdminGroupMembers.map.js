import { addGroupMember, getGroupMembers, getGroupNonMembers, removeGroupMember } from '../../../actions/admin_groups';
import C from '../../../constants';

export const mapAdminGroupMembersProps = state => {
    const MemberUserIds = state.admin_groups.members.map(member => member.UserId);

    return {
        members: state.admin_groups.members.slice(0).sort((member1, member2) => {
            if (member1.UserName > member2.UserName) {
                return 1;
            } else if (member1.UserName < member2.UserName) {
                return -1;
            } else {
                return 0;
            }
        }),
        nonMembers: state.admin_groups.nonMembers
            .filter(user => !user.Hidden || state.admin_groups.showHiddenNonMembers)
            .sort((user1, user2) => {
                if (user1.UserName > user2.UserName) {
                    return 1;
                } else if (user1.UserName < user2.UserName) {
                    return -1;
                } else {
                    return 0;
                }
            }),
        fetchingMembers: state.admin_groups.fetchingMembers,
        fetchingNonMembers: state.admin_groups.fetchingNonMembers,
        fetchingUsers: state.admin_users.fetching
    };
};

export const mapAdminGroupMembersDispatch = dispatch => {
    return {
        addMember(GroupId, UserId, UserName) {
            dispatch(addGroupMember(GroupId, UserId, UserName));
        },

        init(GroupId) {
            dispatch({
                type: C.SET_ADMIN_GROUPS_DATA,
                payload: {
                    showHiddenNonMembers: false,
                    members: [],
                    nonMembers: []
                }
            });
            dispatch(getGroupMembers(GroupId));
            dispatch(getGroupNonMembers(GroupId));
        },

        removeMember(MemberId, UserId, UserName) {
            dispatch(removeGroupMember(MemberId, UserId, UserName));
        },

        setShowHidden(value) {
            dispatch({
                type: C.SET_ADMIN_GROUPS_DATA,
                payload: {
                    showHiddenNonMembers: value
                }
            });
        }
    };
};
