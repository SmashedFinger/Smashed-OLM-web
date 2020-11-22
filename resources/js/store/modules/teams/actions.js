import Vue from 'vue'
import i18n from '../../../i18n'
import router from "../../../routes";

export const actions = {

    /**
     * The user wants to create a new team
     */
    async CREATE_NEW_TEAM (context, payload)
    {
        const title = i18n.t('notifications.success');
        const body  = i18n.t('teams.created');

        const error_title = i18n.t('notifications.error');
        const error_body  = i18n.t('teams.create.max-created');

        await axios.post('/teams/create', {
            name: payload.name,
            identifier: payload.identifier,
            teamType: payload.teamType
        })
        .then(response => {
            console.log('create_new_team', response);

            if (response.data.success)
            {
                // show notification
                Vue.$vToastify.success({
                    title,
                    body,
                    position: 'top-right'
                });

                // add team to teams array
                // context.commit('usersTeams', response.data.team);

                // If user does not have an active team, assign
                if (! context.rootState.user.user.team_id) {
                    context.commit('userJoinTeam', response.data.team);
                }
            }

            else
            {
                Vue.$vToastify.error({
                    title: error_title,
                    body: error_body,
                    position: 'top-right'
                });
            }
        })
        .catch(error => {
            console.error('create_new_team', error.response.data.errors);

            context.commit('teamErrors', error.response.data.errors);
        });
    },

    /**
     * Get the combined effort for all of the users teams for this period
     */
    async GET_COMBINED_TEAM_EFFORT (context, payload)
    {
        await axios.get('/teams/combined-effort', {
            params: {
                period: payload
            }
        })
        .then(response => {
            console.log('get_combined_teams_effort', response);

            context.commit('combinedTeamEffort', response.data);
        })
        .catch(error => {
            console.error('get_combined_teams_effort', error);
        });
    },

    /**
     * Get all team types from DB
     */
    async GET_TEAM_TYPES (context)
    {
        await axios.get('/teams/get-types')
            .then(response => {
                console.log('get_team_types', response);

                context.commit('teamTypes', response.data);
            })
            .catch(error => {
                console.error('get_team_types', error);
            });
    },

    /**
     * The user wants to join a team with an identifier
     */
    async JOIN_TEAM (context, payload)
    {
        await axios.post('/teams/join', {
            identifier: payload
        })
        .then(response => {
            console.log('join_team', response);

            // update translation with response

            // show notification
        })
        .catch(error => {
            console.error('join_team', error);

            context.commit('teamErrors', error.response.data.errors);
        });
    }
}
